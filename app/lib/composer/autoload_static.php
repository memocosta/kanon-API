<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit534b85c9b42089277d6b12b4ad91e7f0
{
    public static $prefixLengthsPsr4 = array (
        'T' => 
        array (
            'Twilio\\' => 7,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Twilio\\' => 
        array (
            0 => __DIR__ . '/..' . '/twilio/sdk/Twilio',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit534b85c9b42089277d6b12b4ad91e7f0::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit534b85c9b42089277d6b12b4ad91e7f0::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
