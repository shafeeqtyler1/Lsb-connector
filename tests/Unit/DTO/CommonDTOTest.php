<?php

declare(strict_types=1);

namespace ShafeeqKt\LsbConnector\Tests\Unit\DTO;

use ShafeeqKt\LsbConnector\Tests\TestCase;
use ShafeeqKt\LsbConnector\DTO\Common\Address;
use ShafeeqKt\LsbConnector\DTO\Common\Phone;
use ShafeeqKt\LsbConnector\DTO\Common\Identification;
use ShafeeqKt\LsbConnector\DTO\Common\CddQuestion;
use ShafeeqKt\LsbConnector\DTO\Common\PersonDetails;
use ShafeeqKt\LsbConnector\DTO\Common\OrganizationDetails;

class CommonDTOTest extends TestCase
{
    public function test_address_to_array(): void
    {
        $address = new Address(
            street: '123 Main St',
            city: 'New York',
            state: 'NY',
            postalCode: '10001',
            country: 'USA',
            streetLine2: 'Suite 100'
        );

        $array = $address->toArray();

        $this->assertEquals('123 Main St', $array['street']);
        $this->assertEquals('Suite 100', $array['street_line_2']);
        $this->assertEquals('New York', $array['city']);
        $this->assertEquals('NY', $array['state']);
        $this->assertEquals('10001', $array['postal_code']);
        $this->assertEquals('USA', $array['country']);
    }

    public function test_address_from_array(): void
    {
        $data = [
            'street' => '456 Oak Ave',
            'street_line_2' => 'Floor 2',
            'city' => 'Los Angeles',
            'state' => 'CA',
            'postal_code' => '90001',
            'country' => 'USA',
        ];

        $address = Address::fromArray($data);

        $this->assertEquals('456 Oak Ave', $address->street);
        $this->assertEquals('Floor 2', $address->streetLine2);
        $this->assertEquals('Los Angeles', $address->city);
        $this->assertEquals('CA', $address->state);
        $this->assertEquals('90001', $address->postalCode);
    }

    public function test_address_excludes_null_values(): void
    {
        $address = new Address(
            street: '123 Main St',
            city: 'New York',
            state: 'NY',
            postalCode: '10001'
        );

        $array = $address->toArray();

        $this->assertArrayNotHasKey('street_line_2', $array);
        $this->assertArrayNotHasKey('region', $array);
    }

    public function test_phone_to_array(): void
    {
        $phone = new Phone(number: '5551234567', countryCode: 'USA');

        $array = $phone->toArray();

        $this->assertEquals('5551234567', $array['number']);
        $this->assertEquals('USA', $array['country_code']);
    }

    public function test_phone_from_array(): void
    {
        $phone = Phone::fromArray(['number' => '1234567890', 'country_code' => 'CAN']);

        $this->assertEquals('1234567890', $phone->number);
        $this->assertEquals('CAN', $phone->countryCode);
    }

    public function test_phone_default_country_code(): void
    {
        $phone = new Phone(number: '5551234567');

        $this->assertEquals('USA', $phone->countryCode);
    }

    public function test_identification_to_array(): void
    {
        $identification = new Identification(
            type: Identification::TYPE_DRIVERS_LICENSE,
            number: '12345678',
            issueDate: '2020-01-01',
            expireDate: '2030-01-01',
            countryCode: 'USA'
        );

        $array = $identification->toArray();

        $this->assertEquals('DRIVERS_LICENSE', $array['type']);
        $this->assertEquals('12345678', $array['number']);
        $this->assertEquals('2020-01-01', $array['issue_date']);
        $this->assertEquals('2030-01-01', $array['expire_date']);
        $this->assertEquals('USA', $array['country_code']);
    }

    public function test_identification_from_array(): void
    {
        $data = [
            'type' => 'PASSPORT',
            'number' => 'AB123456',
            'issue_date' => '2019-05-01',
            'expire_date' => '2029-05-01',
            'country_code' => 'USA',
        ];

        $identification = Identification::fromArray($data);

        $this->assertEquals('PASSPORT', $identification->type);
        $this->assertEquals('AB123456', $identification->number);
    }

    public function test_identification_type_constants(): void
    {
        $this->assertEquals('DRIVERS_LICENSE', Identification::TYPE_DRIVERS_LICENSE);
        $this->assertEquals('PASSPORT', Identification::TYPE_PASSPORT);
        $this->assertEquals('STATE_ID', Identification::TYPE_STATE_ID);
    }

    public function test_cdd_question_to_array(): void
    {
        $question = new CddQuestion(id: '1', answerId: '2');

        $array = $question->toArray();

        $this->assertEquals('1', $array['id']);
        $this->assertEquals('2', $array['answer']['id']);
    }

    public function test_cdd_question_from_array(): void
    {
        $data = ['id' => '1', 'answer' => ['id' => '1']];

        $question = CddQuestion::fromArray($data);

        $this->assertEquals('1', $question->id);
        $this->assertEquals('1', $question->answerId);
    }

    public function test_cdd_question_not_politically_exposed(): void
    {
        $question = CddQuestion::notPoliticallyExposed();

        $this->assertEquals('1', $question->id);
        $this->assertEquals('2', $question->answerId);
    }

    public function test_cdd_question_is_politically_exposed(): void
    {
        $question = CddQuestion::isPoliticallyExposed();

        $this->assertEquals('1', $question->id);
        $this->assertEquals('1', $question->answerId);
    }

    public function test_person_details_to_array(): void
    {
        $personDetails = new PersonDetails(
            firstName: 'John',
            lastName: 'Doe',
            birthDate: '1990-01-15',
            address: new Address('123 Main St', 'New York', 'NY', '10001'),
            phone: new Phone('5551234567'),
            identification: new Identification(
                Identification::TYPE_DRIVERS_LICENSE,
                '12345678',
                '2020-01-01',
                '2030-01-01'
            ),
            taxId: '123456789',
            email: 'john@example.com',
            occupationCode: '15-0000',
            middleName: 'Michael'
        );

        $array = $personDetails->toArray();

        $this->assertEquals('John', $array['first_name']);
        $this->assertEquals('Doe', $array['last_name']);
        $this->assertEquals('Michael', $array['middle_name']);
        $this->assertEquals('1990-01-15', $array['birth_date']);
        $this->assertEquals('123456789', $array['tax_id']);
        $this->assertEquals('john@example.com', $array['email']);
        $this->assertEquals('15-0000', $array['occupation_code']);
        $this->assertArrayHasKey('address', $array);
        $this->assertArrayHasKey('phone', $array);
        $this->assertArrayHasKey('identification', $array);
    }

    public function test_person_details_from_array(): void
    {
        $data = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'birth_date' => '1985-06-20',
            'address' => [
                'street' => '456 Oak Ave',
                'city' => 'Boston',
                'state' => 'MA',
                'postal_code' => '02101',
            ],
            'phone' => ['number' => '5559876543'],
            'identification' => [
                'type' => 'PASSPORT',
                'number' => 'AB123456',
                'issue_date' => '2019-01-01',
                'expire_date' => '2029-01-01',
            ],
            'tax_id' => '987654321',
            'email' => 'jane@example.com',
            'occupation_code' => '11-0000',
        ];

        $personDetails = PersonDetails::fromArray($data);

        $this->assertEquals('Jane', $personDetails->firstName);
        $this->assertEquals('Smith', $personDetails->lastName);
        $this->assertEquals('456 Oak Ave', $personDetails->address->street);
        $this->assertEquals('5559876543', $personDetails->phone->number);
    }

    public function test_organization_details_to_array(): void
    {
        $orgDetails = new OrganizationDetails(
            name: 'Acme Corp LLC',
            formationDate: '2020-01-15',
            address: new Address('789 Business Blvd', 'Chicago', 'IL', '60601'),
            phone: new Phone('5551112222'),
            taxId: '111222333',
            email: 'contact@acme.com',
            naicsCode: '541511',
            dbaName: 'Acme'
        );

        $array = $orgDetails->toArray();

        $this->assertEquals('Acme Corp LLC', $array['name']);
        $this->assertEquals('Acme', $array['dba_name']);
        $this->assertEquals('2020-01-15', $array['formation_date']);
        $this->assertEquals('111222333', $array['tax_id']);
        $this->assertEquals('contact@acme.com', $array['email']);
        $this->assertEquals('541511', $array['naics_code']);
    }

    public function test_organization_details_from_array(): void
    {
        $data = [
            'name' => 'Tech Solutions Inc',
            'dba_name' => 'TechSol',
            'formation_date' => '2015-03-10',
            'address' => [
                'street' => '100 Tech Park',
                'city' => 'San Jose',
                'state' => 'CA',
                'postal_code' => '95101',
            ],
            'phone' => ['number' => '4081234567'],
            'tax_id' => '444555666',
            'email' => 'info@techsol.com',
            'naics_code' => '541512',
        ];

        $orgDetails = OrganizationDetails::fromArray($data);

        $this->assertEquals('Tech Solutions Inc', $orgDetails->name);
        $this->assertEquals('TechSol', $orgDetails->dbaName);
        $this->assertEquals('541512', $orgDetails->naicsCode);
    }
}
