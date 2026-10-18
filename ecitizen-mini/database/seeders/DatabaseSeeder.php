<?php
namespace Database\Seeders;

use App\Models\Department;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $dept = [];
        foreach ([['Business Licensing', 'BL'], ['Lands Registry', 'LR'], ['Criminal Investigations', 'CI'], ['Civil Registration', 'CR']] as [$n, $c]) {
            $dept[$c] = Department::updateOrCreate(['code' => $c], ['name' => $n]);
        }

        $mk = fn ($name, $email, $role, $d = null) => User::updateOrCreate(['email' => $email], [
            'name' => $name, 'password' => 'password', 'role' => $role,
            'department_id' => $d?->id, 'email_verified_at' => now(),
        ]);
        $mk('System Admin', 'admin@ecitizen.test', 'admin');
        $mk('Licensing Officer', 'licensing@ecitizen.test', 'officer', $dept['BL']);
        $mk('Lands Officer', 'lands@ecitizen.test', 'officer', $dept['LR']);
        $mk('DCI Officer', 'dci@ecitizen.test', 'officer', $dept['CI']);
        $mk('Registrar', 'registrar@ecitizen.test', 'officer', $dept['CR']);

        $f = fn ($label, $type = 'text', $req = true, $opts = []) => [
            'name' => Str::slug($label, '_'), 'label' => $label, 'type' => $type, 'required' => $req, 'options' => $opts,
        ];

        $services = [
            ['BL', 'Business Permit', 'Business', 'Register a new business and obtain a trading permit.', 3000, 7,
                [$f('Business name'), $f('Business type', 'select', true, ['Retail', 'Wholesale', 'Services', 'Manufacturing']), $f('Physical location'), $f('KRA PIN')],
                ['National ID / Passport copy', 'Lease or ownership proof']],
            ['BL', 'Single Business Permit', 'Business', 'Annual single business permit renewal.', 5000, 5,
                [$f('Business name'), $f('Previous permit number', 'text', false), $f('Number of employees', 'number')],
                ['National ID / Passport copy']],
            ['LR', 'Land Search', 'Land', 'Official search of a land parcel record.', 500, 3,
                [$f('Parcel number'), $f('Registry / County'), $f('Purpose of search', 'textarea', false)],
                ['National ID / Passport copy']],
            ['CI', 'Good Conduct Certificate', 'Certificates', 'Certificate of good conduct for employment or travel.', 1000, 10,
                [$f('Date of birth', 'date'), $f('Place of birth'), $f('Purpose', 'select', true, ['Employment', 'Travel / Visa', 'Education', 'Other'])],
                ['National ID / Passport copy', 'Passport photo']],
            ['CR', 'Certificate Request', 'Certificates', 'Request a copy of a birth, death or marriage certificate (free).', 0, 5,
                [$f('Certificate type', 'select', true, ['Birth', 'Death', 'Marriage']), $f('Full name on certificate'), $f('Date of event', 'date'), $f('Place of event')],
                ['Supporting ID or legal document']],
        ];
        foreach ($services as [$d, $name, $cat, $desc, $fee, $sla, $fields, $docs]) {
            Service::updateOrCreate(['name' => $name], [
                'department_id' => $dept[$d]->id, 'category' => $cat, 'description' => $desc, 'fee' => $fee,
                'sla_days' => $sla, 'fields' => $fields, 'required_documents' => $docs, 'is_active' => true,
            ]);
        }
    }
}
