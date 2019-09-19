<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit8dad0284af96de352af66dbdd0f1058b
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'PHPMailer\\PHPMailer\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'PHPMailer\\PHPMailer\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpmailer/phpmailer/src',
        ),
    );

    public static $classMap = array (
        'Bulletproof\\Image' => __DIR__ . '/..' . '/samayo/bulletproof/src/bulletproof.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit8dad0284af96de352af66dbdd0f1058b::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit8dad0284af96de352af66dbdd0f1058b::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit8dad0284af96de352af66dbdd0f1058b::$classMap;

        }, null, ClassLoader::class);
    }
}