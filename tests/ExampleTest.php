<?php
//
//use Illuminate\Foundation\Testing\WithoutMiddleware;
//use Illuminate\Foundation\Testing\DatabaseMigrations;
//use Illuminate\Foundation\Testing\DatabaseTransactions;
//
//class ExampleTest extends TestCase
//{
//    /**
//     * A basic functional test example.
//     *
//     * @return void
//     */
//    public function testBasicExample()
//    {
//        $this->visit('/')
//             ->see('BEAUTY');
//    }
//
//    /**
//     * A basic functional test example.
//     *
//     * @return void
//     */
//    public function test_goto_checkout()
//    {
//        $this->visit('/checkout')
//            ->see('Оформление заказа')
//            ->see('Авторизация')
//            ->see('Email-адрес')
//            ->see('Войти')
//            ->see('Зарегистрироваться')
//            ->see('Купить без регистрации')
//            ->see('Пароль');
//
//    }
//
//    public function testNewUserRegistration()
//    {
//        $this->clearUserTable();
//
//        $this->visit('/register')
//            ->type('John', 'firstname')
//            ->type('Doe', 'lastname')
//            ->type('b1@parfum.ru', 'email')
//            ->type('Daktil345', 'password')
//            ->type('Daktil345', 'password_confirmation')
//            ->check('agreement')
//            ->press('Регистрация')
//            ->seePageIs('/');
//    }
//
//    public function test_firstname_not_inputed()
//    {
//        $this->clearUserTable();
//
//        $this->visit('/register')
//            //->type('John', 'firstname')
//            ->type('Doe', 'lastname')
//            ->type('b1@parfum.ru', 'email')
//            ->type('Daktil345', 'password')
//            ->type('Daktil345', 'password_confirmation')
//            ->check('agreement')
//            ->press('Регистрация')
//            ->see('Поле firstname обязательно к заполнению!');
//    }
//    public function test_lastname_not_inputed()
//    {
//        $this->clearUserTable();
//
//        $this->visit('/register')
//            ->type('John', 'firstname')
//            //->type('Doe', 'lastname')
//            ->type('b1@parfum.ru', 'email')
//            ->type('Daktil345', 'password')
//            ->type('Daktil345', 'password_confirmation')
//            ->check('agreement')
//            ->press('Регистрация')
//            ->see('Поле lastname обязательно к заполнению!');
//    }
//
//    public function test_email_not_inputed()
//    {
//        $this->clearUserTable();
//
//        $this->visit('/register')
//            ->type('John', 'firstname')
//            ->type('Doe', 'lastname')
//            //->type('b1@parfum.ru', 'email')
//            ->type('Daktil345', 'password')
//            ->type('Daktil345', 'password_confirmation')
//            ->check('agreement')
//            ->press('Регистрация')
//            ->see('Поле email обязательно к заполнению!');
//    }
//
//    public function test_password_not_inputed()
//    {
//        $this->clearUserTable();
//
//        $this->visit('/register')
//            ->type('John', 'firstname')
//            ->type('Doe', 'lastname')
//            ->type('b1@parfum.ru', 'email')
//            //->type('Daktil345', 'password')
//            ->type('Daktil345', 'password_confirmation')
//            ->check('agreement')
//            ->press('Регистрация')
//            ->see('Поле password обязательно к заполнению!');
//    }
//
//    public function test_password_confirmation_not_inputed()
//    {
//        $this->clearUserTable();
//
//        $this->visit('/register')
//            ->type('John', 'firstname')
//            ->type('Doe', 'lastname')
//            ->type('b1@parfum.ru', 'email')
//            ->type('Daktil345', 'password')
//            //->type('Daktil345', 'password_confirmation')
//            ->check('agreement')
//            ->press('Регистрация')
//            ->see('Введенные поля password не совпадают');
//    }
//
//    public function test_agreement_not_inputed()
//    {
//        $this->clearUserTable();
//
//        $this->visit('/register')
//            ->type('John', 'firstname')
//            ->type('Doe', 'lastname')
//            ->type('b1@parfum.ru', 'email')
//            ->type('Daktil345', 'password')
//            ->type('Daktil345', 'password_confirmation')
//            //->check('agreement')
//            ->press('Регистрация')
//            ->see('Для регистрации необходимо согласие с условиями обслуживания!');
//    }
//
//    function getUserData()
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
//            'agreement' => '',
//            'newsletter' => ''
//        ];
//    }
//
//    public function checkExistNewData($data,$table)
//    {
//        $data = $this->clearEmptyData($data);
//
//        $this->seeInDatabase($table, $data);
//    }
//
//    public function clearEmptyData($data)
//    {
//        return array_filter($data,function($entry){
//            return ! (empty($entry) || $entry == 'password');
//        });
//    }
//
//    public function test_registracia_fiz_lica()
//    {
//        $this->clearUserTable();
//
//        $data = $this->getUserData();
//
//        $data['type_id'] = 2;
//
//        app('ClientFactory')->register($data);
//
//        $this->checkExistNewData($data,'users');
//    }
//
//    public function test_registracia_urlica_lica()
//    {
//        $this->clearUserTable();
//
//        $data = $this->getUserData();
//
//        $data['type_id'] = 3;
//
//        app('ClientFactory')->register($data);
//
//        $this->checkExistNewData($data,'users');
//    }
//
//    public function clearUserTable()
//    {
//        \App\Core\Models\Users::truncate();
//    }
//}
