<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\JobApplication;
use App\Models\JobVacancy;
use App\Models\RecruitmentPeriod;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RecruitmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_links_to_recruitment_page(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(route('recruitment.index'), false)
            ->assertSee('Recruitment');
    }

    public function test_public_page_only_displays_open_vacancies_from_active_period(): void
    {
        [$period, $branch, $open] = $this->createVacancy();
        $closed = JobVacancy::query()->create([
            'recruitment_period_id' => $period->id,
            'category' => 'Administrasi',
            'work_type' => 'Penuh Waktu',
            'title' => 'Posisi Ditutup',
            'description' => 'Tidak boleh tampil.',
            'qualification' => 'D3',
            'compensation' => 'Gaji kompetitif',
            'quota' => 1,
            'status' => JobVacancy::STATUS_CLOSED,
        ]);
        $closed->branches()->attach($branch);

        $this->get(route('recruitment.index'))
            ->assertOk()
            ->assertSee($period->name)
            ->assertSee($open->title)
            ->assertSee($branch->name)
            ->assertDontSee($closed->title)
            ->assertSee('Lamar Posisi');
    }

    public function test_application_is_stored_with_encrypted_nik_and_private_pdf(): void
    {
        Storage::fake('local');
        [, $branch, $vacancy] = $this->createVacancy();

        $response = $this->post(route('recruitment.applications.store'), [
            'full_name' => 'I Wayan Pelamar',
            'nik' => '5171010101010001',
            'whatsapp' => '081234567890',
            'email' => 'pelamar@example.com',
            'job_vacancy_id' => $vacancy->id,
            'domicile' => 'Kota Denpasar',
            'branch_id' => $branch->id,
            'experience' => 'Dua tahun pengalaman operasional.',
            'document' => UploadedFile::fake()->create('berkas-lamaran.pdf', 800, 'application/pdf'),
            'consent' => '1',
        ]);

        $response->assertRedirect(route('recruitment.index'))->assertSessionHas('application_status');
        $application = JobApplication::query()->firstOrFail();
        $this->assertSame('5171010101010001', $application->nik);
        $this->assertNotSame('5171010101010001', DB::table('job_applications')->value('nik'));
        $this->assertSame($branch->id, $application->branch_id);
        Storage::disk('local')->assertExists($application->document_path);
        Storage::disk('public')->assertMissing($application->document_path);
    }

    public function test_same_nik_cannot_apply_twice_to_same_vacancy(): void
    {
        Storage::fake('local');
        [, $branch, $vacancy] = $this->createVacancy();
        JobApplication::query()->create([
            'job_vacancy_id' => $vacancy->id,
            'branch_id' => $branch->id,
            'full_name' => 'Pelamar Pertama',
            'nik' => '5171010101010001',
            'nik_hash' => JobApplication::nikHash('5171010101010001'),
            'whatsapp' => '081234567890',
            'email' => 'first@example.com',
            'domicile' => 'Denpasar',
            'document_path' => 'recruitment-applications/existing.pdf',
            'document_original_name' => 'existing.pdf',
            'consent_at' => now(),
        ]);

        $this->from(route('recruitment.index'))->post(route('recruitment.applications.store'), [
            'full_name' => 'Pelamar Kedua',
            'nik' => '5171010101010001',
            'whatsapp' => '081234567891',
            'email' => 'second@example.com',
            'job_vacancy_id' => $vacancy->id,
            'domicile' => 'Badung',
            'branch_id' => $branch->id,
            'document' => UploadedFile::fake()->create('berkas.pdf', 100, 'application/pdf'),
            'consent' => '1',
        ])->assertRedirect(route('recruitment.index'))->assertSessionHasErrors('nik');

        $this->assertDatabaseCount('job_applications', 1);
    }

    public function test_application_rejects_unavailable_branch_and_non_pdf_file(): void
    {
        Storage::fake('local');
        [, , $vacancy] = $this->createVacancy();
        $otherBranch = Branch::factory()->create();

        $this->from(route('recruitment.index'))->post(route('recruitment.applications.store'), [
            'full_name' => 'Pelamar Salah',
            'nik' => '5171010101010002',
            'whatsapp' => '081234567890',
            'email' => 'invalid@example.com',
            'job_vacancy_id' => $vacancy->id,
            'domicile' => 'Gianyar',
            'branch_id' => $otherBranch->id,
            'document' => UploadedFile::fake()->create('berkas.zip', 100, 'application/zip'),
            'consent' => '1',
        ])->assertRedirect(route('recruitment.index'))->assertSessionHasErrors(['branch_id', 'document']);

        $this->assertDatabaseEmpty('job_applications');
    }

    public function test_application_rejects_inactive_branch_even_when_it_is_attached_to_vacancy(): void
    {
        Storage::fake('local');
        [, , $vacancy] = $this->createVacancy();
        $inactiveBranch = Branch::factory()->create(['status' => Branch::STATUS_INACTIVE]);
        $vacancy->branches()->attach($inactiveBranch);

        $this->from(route('recruitment.index'))->post(route('recruitment.applications.store'), [
            'full_name' => 'Pelamar Cabang Nonaktif',
            'nik' => '5171010101010010',
            'whatsapp' => '081234567890',
            'email' => 'inactive@example.com',
            'job_vacancy_id' => $vacancy->id,
            'domicile' => 'Denpasar',
            'branch_id' => $inactiveBranch->id,
            'document' => UploadedFile::fake()->create('berkas.pdf', 100, 'application/pdf'),
            'consent' => '1',
        ])->assertRedirect(route('recruitment.index'))->assertSessionHasErrors('branch_id');

        $this->assertDatabaseEmpty('job_applications');
    }

    public function test_recruitment_whatsapp_is_managed_from_system_settings(): void
    {
        SystemSetting::put('recruitment_whatsapp', '0812-3849-0291');

        $this->get(route('recruitment.index'))
            ->assertOk()
            ->assertSee('0812-3849-0291')
            ->assertSee('https://wa.me/6281238490291', false);
    }

    public function test_only_main_admin_can_access_recruitment_administration(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $branchAdmin = User::factory()->create([
            'role' => User::ROLE_BRANCH_ADMIN,
            'branch_id' => Branch::factory()->create()->id,
        ]);

        $this->actingAs($admin)->get(route('admin.recruitment.index'))->assertOk();
        $this->actingAs($branchAdmin)->get(route('admin.recruitment.index'))->assertForbidden();
    }

    public function test_main_admin_can_create_vacancy_for_multiple_branches(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $period = RecruitmentPeriod::query()->create(['name' => 'Kuartal 1 / 2027', 'is_active' => true]);
        $branches = Branch::factory()->count(2)->create();

        $this->actingAs($admin)->post(route('admin.job-vacancies.store'), [
            'recruitment_period_id' => $period->id,
            'category' => 'Armada & Driver',
            'work_type' => 'Penuh Waktu',
            'title' => 'Driver Operasional Bank',
            'description' => 'Mengemudikan kendaraan dinas perbankan.',
            'qualification' => 'Wajib SIM A aktif.',
            'compensation' => 'Gaji UMK dan BPJS.',
            'quota' => 8,
            'branch_ids' => $branches->pluck('id')->all(),
        ])->assertRedirect(route('admin.recruitment.index'));

        $vacancy = JobVacancy::query()->firstOrFail();
        $this->assertCount(2, $vacancy->branches);
        $this->assertSame(JobVacancy::STATUS_OPEN, $vacancy->status);
        $this->assertSame(1, $vacancy->sort_order);
    }

    public function test_recruitment_admin_renders_periods_vacancies_and_applicants_on_one_page(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        [, , $vacancy] = $this->createVacancy();

        $this->actingAs($admin)->get(route('admin.recruitment.index'))
            ->assertOk()
            ->assertSee('Lowongan &amp; Gelombang', false)
            ->assertSee('Tambah Gelombang')
            ->assertSee('Tambah Lowongan')
            ->assertSee($vacancy->title)
            ->assertSee('Cabang Penempatan');

        $this->get(route('admin.recruitment.index', ['tab' => 'applicants']))
            ->assertOk()
            ->assertSee('Data Pelamar');
    }

    public function test_creating_a_wave_automatically_activates_it_and_records_today(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $oldPeriod = RecruitmentPeriod::query()->create(['name' => 'Periode Lama', 'is_active' => true]);

        $this->actingAs($admin)->post(route('admin.recruitment-periods.store'), [
            'name' => 'Periode Baru',
            'starts_at' => '2027-04-01',
            'ends_at' => '2027-06-30',
        ])->assertRedirect(route('admin.recruitment.index'));

        $this->assertFalse($oldPeriod->fresh()->is_active);
        $newPeriod = RecruitmentPeriod::query()->where('name', 'Periode Baru')->firstOrFail();
        $this->assertTrue($newPeriod->is_active);
        $this->assertTrue($newPeriod->starts_at->isToday());
        $this->assertNull($newPeriod->ends_at);
    }

    public function test_period_dates_from_the_request_are_ignored_when_creating_a_wave(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)->post(route('admin.recruitment-periods.store'), [
            'name' => 'Periode Tanpa Tanggal',
            'starts_at' => '2027-01-01',
            'ends_at' => '2027-12-31',
        ])->assertRedirect(route('admin.recruitment.index'));

        $period = RecruitmentPeriod::query()->where('name', 'Periode Tanpa Tanggal')->firstOrFail();
        $this->assertTrue($period->is_active);
        $this->assertTrue($period->starts_at->isToday());
        $this->assertNull($period->ends_at);
    }

    public function test_admin_can_toggle_a_period_active_or_inactive_without_editing_it(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $activePeriod = RecruitmentPeriod::query()->create(['name' => 'Periode Aktif', 'is_active' => true]);
        $otherPeriod = RecruitmentPeriod::query()->create(['name' => 'Periode Lain', 'is_active' => false]);

        $this->actingAs($admin)->patch(route('admin.recruitment-periods.active', $otherPeriod), [
            'is_active' => '1',
        ])->assertRedirect();

        $this->assertFalse($activePeriod->fresh()->is_active);
        $this->assertTrue($otherPeriod->fresh()->is_active);

        $this->actingAs($admin)->patch(route('admin.recruitment-periods.active', $otherPeriod))
            ->assertRedirect();

        $this->assertFalse($otherPeriod->fresh()->is_active);
        $this->assertDatabaseMissing('recruitment_periods', ['is_active' => true]);
    }

    public function test_admin_can_toggle_a_vacancy_and_new_vacancies_are_opened_at_the_end_of_the_wave(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        [$period, $branch, $existingVacancy] = $this->createVacancy();
        $existingVacancy->update(['sort_order' => 4]);

        $this->actingAs($admin)->post(route('admin.job-vacancies.store'), [
            'recruitment_period_id' => $period->id,
            'category' => 'Administrasi',
            'work_type' => 'Penuh Waktu',
            'title' => 'Staf Administrasi',
            'description' => 'Mendukung administrasi operasional.',
            'qualification' => 'Teliti dan mahir menggunakan komputer.',
            'compensation' => 'Gaji UMK dan BPJS.',
            'quota' => 2,
            'branch_ids' => [$branch->id],
        ])->assertRedirect(route('admin.recruitment.index'));

        $vacancy = JobVacancy::query()->where('title', 'Staf Administrasi')->firstOrFail();
        $this->assertSame(JobVacancy::STATUS_OPEN, $vacancy->status);
        $this->assertSame(5, $vacancy->sort_order);

        $this->actingAs($admin)->patch(route('admin.job-vacancies.active', $vacancy))
            ->assertRedirect();

        $this->assertSame(JobVacancy::STATUS_CLOSED, $vacancy->fresh()->status);
    }

    public function test_admin_can_download_application_pdf_from_private_storage(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        [, $branch, $vacancy] = $this->createVacancy();
        Storage::disk('local')->put('recruitment-applications/test.pdf', '%PDF-1.4 test');
        $application = JobApplication::query()->create([
            'job_vacancy_id' => $vacancy->id,
            'branch_id' => $branch->id,
            'full_name' => 'Pelamar Download',
            'nik' => '5171010101010003',
            'nik_hash' => JobApplication::nikHash('5171010101010003'),
            'whatsapp' => '081234567890',
            'email' => 'download@example.com',
            'domicile' => 'Denpasar',
            'document_path' => 'recruitment-applications/test.pdf',
            'document_original_name' => 'berkas.pdf',
            'consent_at' => now(),
        ]);

        $this->actingAs($admin)->get(route('admin.job-applications.document', $application))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    /** @return array{RecruitmentPeriod, Branch, JobVacancy} */
    private function createVacancy(): array
    {
        $period = RecruitmentPeriod::query()->create([
            'name' => 'Formasi Terbuka Kuartal 1 / 2027',
            'is_active' => true,
        ]);
        $branch = Branch::factory()->create(['status' => Branch::STATUS_ACTIVE]);
        $vacancy = JobVacancy::query()->create([
            'recruitment_period_id' => $period->id,
            'category' => 'Armada & Driver',
            'work_type' => 'Penuh Waktu',
            'title' => 'Driver Operasional Bank',
            'description' => 'Mengemudikan kendaraan dinas perbankan dan menjaga perjalanan kedinasan.',
            'qualification' => 'Wajib SIM A / B1 aktif dan rekam jejak aman.',
            'compensation' => 'Gaji UMK + uang perjalanan + lembur.',
            'quota' => 8,
            'sort_order' => 1,
            'status' => JobVacancy::STATUS_OPEN,
        ]);
        $vacancy->branches()->attach($branch);

        return [$period, $branch, $vacancy];
    }
}
