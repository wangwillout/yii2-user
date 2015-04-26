<?php

/*
 * This file is part of the Dektrium project.
 *
 * (c) Dektrium project <http://github.com/dektrium/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace dektrium\user\models;

use dektrium\user\Module;
use yii\base\Model;

/**
 * Registration form collects user input on registration process, validates it and creates new User model.
 *
 * @author Dmitry Erofeev <dmeroff@gmail.com>
 */
class RegistrationForm extends Model
{
    /**
     * @var string User email address
     */
    public $email;

    /**
     * @var string Username
     */
    public $username;

    /**
     * @var string Password
     */
    public $password;

    /**
     * @var Module
     */
    protected $module;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->module = \Yii::$app->getModule('user');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $user = $this->module->modelMap['User'];
        return [
            // username rules
            'usernameLength'   => ['username', 'string', 'min' => 3, 'max' => 20],
            'usernameTrim'     => ['username', 'filter', 'filter' => 'trim'],
            'usernamePattern'  => ['username', 'match', 'pattern' => $user::$usernameRegexp],
            'usernameRequired' => ['username', 'required'],
            'usernameUnique'   => ['username', 'unique', 'targetClass' => $user,
                'message' => \Yii::t('user', 'This username has already been taken')],

            // email rules
            'emailTrim'     => ['email', 'filter', 'filter' => 'trim'],
            'emailRequired' => ['email', 'required'],
            'emailPattern'  => ['email', 'email'],
            'emailUnique'   => ['email', 'unique', 'targetClass' => $user,
                'message' => \Yii::t('user', 'This email address has already been taken')],

            // password rules
            'passwordRequired' => ['password', 'required', 'skipOnEmpty' => $this->module->enableGeneratingPassword],
            'passwordLength'   => ['password', 'string', 'min' => 6],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'email'    => \Yii::t('user', 'Email'),
            'username' => \Yii::t('user', 'Username'),
            'password' => \Yii::t('user', 'Password'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function formName()
    {
        return 'register-form';
    }

    /**
     * Registers a new user account. If registration was successful it will set flash message.
     *
     * @return bool
     */
    public function register()
    {
        if (!$this->validate()) {
            return false;
        }

        /** @var User $user */
        $user = \Yii::createObject(User::className());
        $user->setScenario('register');
        $user->setAttributes($this->attributes);

        if (!$user->register()) {
            return false;
        }

        \Yii::$app->session->setFlash(
            'info',
            \Yii::t('user', 'Your account has been created and a message with further instructions has been sent to your email')
        );

        return true;
    }
}
