<?php

namespace Captcha\Bundle\CaptchaBundle\Helpers;

use Captcha\Bundle\CaptchaBundle\Support\LibraryLoader;
use Captcha\Bundle\CaptchaBundle\Support\UserCaptchaConfiguration;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Validator\Exception\InvalidArgumentException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BotDetectCaptchaHelper
{
    /**
     * @var object
     */
    private $captcha;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Constructor.
     *
     * @param  SessionInterface  $session
     * @param  string            $configName
     *
     * @return void
     */
    public function __construct(SessionInterface $session, $configName, ContainerInterface $container)
    {
        $this->container = $container;

        // load BotDetect Library
        LibraryLoader::load($session);

        // get captcha config
        $captchaId = $configName;
        $captchaConfig = UserCaptchaConfiguration::get($captchaId, $container);

        if (null === $captchaConfig) {
            throw new InvalidArgumentException(sprintf('The "%s" option could not be found in app/config/captcha.php file.', $captchaId));
        }

        if (!is_array($captchaConfig)) {
            throw new UnexpectedTypeException($captchaConfig, 'array');
        }

        // save user's captcha configuration options
        UserCaptchaConfiguration::save($captchaConfig);

        // create a BotDetect Captcha object instance
        $this->initCaptcha($captchaConfig);
    }

    /**
     * Initialize CAPTCHA object instance.
     *
     * @param  array  $config
     * 
     * @return void
     */
    public function initCaptcha(array $config)
    {
        // set captchaId and create an instance of Captcha
        $captchaId = (array_key_exists('CaptchaId', $config)) ? $config['CaptchaId'] : 'defaultCaptchaId';
        $this->captcha = new \Captcha($captchaId);

        // set user's input id
        if (array_key_exists('UserInputID', $config)) {
            $this->captcha->UserInputID = $config['UserInputID'];
        }
    }

    public function __call($method, $args = array())
    {
        if (method_exists($this, $method)) {
            return call_user_func_array(array($this, $method), $args);
        }

        if (method_exists($this->captcha, $method)) {
            return call_user_func_array(array($this->captcha, $method), $args);
        }
    }

    /**
     * Auto-magic helpers for civilized property access.
     */
    public function __get($name)
    {
        if (method_exists($this->captcha, ($method = 'get_'.$name))) {
            return $this->captcha->$method();
        }

        if (method_exists($this, ($method = 'get_'.$name))) {
            return $this->$method();
        }
    }

    public function __isset($name)
    {
        if (method_exists($this->captcha, ($method = 'isset_'.$name))) {
            return $this->captcha->$method();
        } 

        if (method_exists($this, ($method = 'isset_'.$name))) {
            return $this->$method();
        }
    }

    public function __set($name, $value)
    {
        if (method_exists($this->captcha, ($method = 'set_'.$name))) {
            $this->captcha->$method($value);
        } else if (method_exists($this, ($method = 'set_'.$name))) {
            $this->$method($value);
        }
    }

    public function __unset($name)
    {
        if (method_exists($this->captcha, ($method = 'unset_'.$name))) {
            $this->captcha->$method();
        } else if (method_exists($this, ($method = 'unset_'.$name))) {
            $this->$method();
        }
    }
}
