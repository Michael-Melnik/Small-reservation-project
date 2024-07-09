<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Mail\RegistrationInvite;
use Illuminate\Support\Facades\Mail;
use App\Enums\Role;
use Tests\TestCase;

class CompanyGuideTest extends TestCase
{
   use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
    }

    public function test_company_owner_can_view_his_companies_guides()
    {
        $company = Company::factory()->create();
        $user = User::factory()->companyOwner()->create(['company_id' => $company->id]);
        $secondUser = User::factory()->guide()->create(['company_id' => $company->id]);

        $responce = $this->actingAs($user)->get(route('companies.guides.index', $company->id));

        $responce->assertOk()->assertSeeText($secondUser->name);
   }

    public function test_company_owner_cannot_view_other_companies_guides()
    {
        $company = Company::factory()->create();
        $company2 = Company::factory()->create();
        $user = User::factory()->companyOwner()->create(['company_id' => $company->id]);

        $responce = $this->actingAs($user)->get(route('companies.guides.index', $company2->id));

        $responce->assertForbidden();
    }

    public function test_company_owner_can_send_invite_to_guide_to_his_company()
    {
        Mail::fake();

        $company = Company::factory()->create();
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->post(route('companies.guides.store', $company->id), [
            'email' => 'test@test.com',
        ]);

        Mail::assertSent(RegistrationInvite::class);

        $response->assertRedirect(route('companies.guides.index', $company->id));

        $this->assertDatabaseHas('user_invitations', [
            'email' => 'test@test.com',
            'registered_at' => null,
            'company_id' => $company->id,
            'role_id' => Role::GUIDE->value,
        ]);
    }

    public function test_invitation_can_be_sent_only_once_for_user()
    {
        $company = Company::factory()->create();
        $user = User::factory()->companyOwner()->create(['company_id' => $company]);

        $this->actingAs($user)->post(route('companies.guides.store', $company->id), [
            'email' => 'test@test.com',
        ]);

        $response = $this->actingAs($user)->post(route('companies.guides.store', $company->id), [
            'email' => 'test@test.com',
        ]);

        $response->assertInvalid(['email' => 'Invitation with this email address already requested.']);
    }


    public function test_company_owner_cannot_create_guide_to_other_company()
    {
        $company = Company::factory()->create();
        $company2 = Company::factory()->create();
        $user = User::factory()->companyOwner()->create(['company_id' => $company->id]);

        $response = $this->actingAs($user)->post(route('companies.guides.store', $company2->id), [
            'name' => 'user test',
            'email' => 'test@test.com',
            'password' => 'password',
        ]);

        $response->assertForbidden();
    }

    public function test_company_owner_can_edit_guide_for_his_company()
    {
        $company = Company::factory()->create();
        $user = User::factory()->companyOwner()->create(['company_id' => $company->id]);

        $response = $this->actingAs($user)->put(route('companies.guides.update', [$company->id, $user->id]), [
            'name' => 'update user',
            'email' => 'update@test.com',
        ]);

        $response->assertRedirect(route('companies.guides.index', $company));

        $this->assertDatabaseHas('users', [
            'name' => 'update user',
            'email' => 'update@test.com',
            'company_id' => $company->id,
        ]);

    }

    public function test_company_owner_cannot_edit_guide_for_other_company()
    {
        $company = Company::factory()->create();
        $company2 = Company::factory()->create();
        $user = User::factory()->companyOwner()->create(['company_id' => $company->id]);

        $response = $this->actingAs($user)->put(route('companies.guides.update', [$company2->id, $user->id]), [
            'name' => 'update user',
            'email' => 'update@test.com',
        ]);

       $response->assertForbidden();

    }

    public function test_company_owner_can_delete_guide_for_his_company()
    {
        $company = Company::factory()->create();
        $user = User::factory()->companyOwner()->create(['company_id' => $company->id]);

        $response = $this->actingAs($user)->delete(route('companies.guides.destroy', [$company->id, $user->id]));

        $response->assertRedirect(route('companies.guides.index', $company));

        $this->assertDatabaseMissing('users', [
            'name' => 'update user',
            'email' => 'update@test.com',
            'company_id' => $company->id,
        ]);
    }

    public function test_company_owner_cannot_delete_guide_for_other_company()
    {
        $company = Company::factory()->create();
        $company2 = Company::factory()->create();
        $user = User::factory()->companyOwner()->create(['company_id' => $company->id]);

        $response = $this->actingAs($user)->delete(route('companies.guides.destroy', [$company2->id, $user->id]));

        $response->assertForbidden();
    }


}
