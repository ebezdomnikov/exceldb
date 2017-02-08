<?php
//
//use Illuminate\Foundation\Testing\WithoutMiddleware;
//use Illuminate\Foundation\Testing\DatabaseMigrations;
//use Illuminate\Foundation\Testing\DatabaseTransactions;
//
//class RegistrationTest extends TestCase
//{
//    use DatabaseTransactions;
//    /**
//     * Данные клиента на входе
//     *
//     * @return array
//     */
//    function getInputUserData()
//    {
//        return [
//            'shop_id' => 1,
//            'gender_id' => 3,
//            'default_price_id' => 12,
//            'firstname' => 'John',
//            'lastname' => 'Doe',
//            'middlename' => '',
//            'email' => 'jon@doe.com',
//            'password' => 'password',
//            'agreement' => 'Y',
//            'newsletter' => 'Y'
//        ];
//    }
//
//    public function clearEmptyData($data)
//    {
//        return array_filter($data,function($entry){
//            return ! (empty($entry) || $entry == 'password');
//        });
//    }
//
//    /** @test */
//    public function it_should_register_guest_if_type_is_empty()
//    {
//        $data = $this->getInputUserData();
//
//        $data['type'] = '';
//
//        app('ClientFactory')->register($data);
//
//        $data['type_id'] = '0';
//
//        $data = $this->clearEmptyData($data);
//
//        $this->seeInDatabase('users', $data);
//
//    }
//
//    /** @test */
//    public function it_should_register_guest()
//    {
//        $data = $this->getInputUserData();
//
//        $data['type'] = 0;
//        $data['agreement'] = 'Y';
//
//        app('ClientFactory')->register($data);
//
//        $data = $this->clearEmptyData($data);
//
//        $this->seeInDatabase('users', $data);
//    }
//
//    /** @test */
//    public function it_should_register_person()
//    {
//        $data = $this->getInputUserData();
//
//        $data['type'] = 'person';
//
//        app('ClientFactory')->register($data);
//
//        $data = $this->clearEmptyData($data);
//
//        $data['type_id'] = '2';
//
//        $this->seeInDatabase('users', $data);
//    }
//
//    /** @test */
//    public function it_should_register_company()
//    {
//        $data = $this->getInputUserData();
//
//        $data['type'] = 'company';
//
//        app('ClientFactory')->register($data);
//
//        $data = $this->clearEmptyData($data);
//
//        $data['type_id'] = '3';
//
//        $this->seeInDatabase('users', $data);
//    }
//
//    /** @test */
//    public function it_should_not_register_in_not_agree_with_agreement()
//    {
//        $data = $this->getInputUserData();
//
//        $data['type'] = 'person';
//        $data['agreement'] = '';
//
//        $this->setExpectedException('Exception');
//
//        app('ClientFactory')->register($data);
//
//        $data = $this->clearEmptyData($data);
//
//        $data['type_id'] = '2';
//
//        $this->seeInDatabase('users', $data);
//
//    }
//
//    /** @test */
//    public function it_should_not_register_if_user_exist_with_same_email()
//    {
//        $data = $this->getInputUserData();
//
//        $data['type'] = 'person';
//        $data['agreement'] = 'Y';
//
//        $this->setExpectedException('Exception');
//
//        //Register same user twice.
//        app('ClientFactory')->register($data);
//        app('ClientFactory')->register($data);
//    }
//}
