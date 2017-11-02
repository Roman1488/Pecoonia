<?php
namespace App\Traits;
use Crypt;

trait Encryptable
{
    //env('AES_KEY') is our base64 encoded 256bit AESKey that we created earlier. You will probably store and define this AESKey in a config file.

    private static $encValSeparator = "::pecoonia::";
    private static $initVector      = "8";

    public function my_encrypt($data)
    {
        // Generate an initialization vector
        $iv = str_repeat(self::$initVector, openssl_cipher_iv_length('aes-256-cbc'));

        // Encrypt the data using AES 256 encryption in CBC mode using our encryption AESKey and initialization vector.
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', env('AES_KEY'), 0, $iv);

        // The $iv is just as important as the AESKey for decrypting, so save it with our encrypted data using a unique separator

        return base64_encode($encrypted . self::$encValSeparator . $iv);
    }

    public function my_decrypt($data)
    {
        if (strpos(base64_decode($data), self::$encValSeparator) === FALSE)
        {
            return $data;
        }

        // To decrypt, split the encrypted data from our IV

        list($encrypted_data, $iv) = explode(self::$encValSeparator, base64_decode($data), 2);

        return openssl_decrypt($encrypted_data, 'aes-256-cbc', env('AES_KEY'), 0, $iv);
    }

    public function setAttribute($key, $value)
    {
        if(isset($this->encryptCrypt))
        {
            if (in_array($key, $this->encryptCrypt))
            {
                $value = Crypt::encrypt($value);
            }
        }

        if(isset($this->encryptBase64))
        {
            if (in_array($key, $this->encryptBase64))
            {
                $value = base64_encode($value);
            }
        }

        if(isset($this->encryptAES256))
        {
            if (in_array($key, $this->encryptAES256))
            {
                $value = $this->my_encrypt($value);
            }
        }

        return parent::setAttribute($key, $value);
    }

    public function getAttribute($key)
    {
        if(isset($this->encryptCrypt))
        {
            if (in_array($key, $this->encryptCrypt))
            {
                return Crypt::decrypt($this->attributes[$key]);
            }
        }

        if(isset($this->encryptBase64))
        {
            if (in_array($key, $this->encryptBase64))
            {
                return base64_decode($this->attributes[$key]);
            }
        }

        if(isset($this->encryptAES256))
        {
            if (in_array($key, $this->encryptAES256))
            {
                return $this->my_decrypt($this->attributes[$key]);
            }
        }

        return parent::getAttribute($key);
    }

    public function attributesToArray()
    {
        $attributes = parent::attributesToArray();

        if(isset($this->encryptCrypt))
        {
            foreach ($attributes as $key => $value)
            {
                if (in_array($key, $this->encryptCrypt))
                {
                    $attributes[$key] = Crypt::decrypt($value);
                }
            }
        }

        if(isset($this->encryptBase64))
        {
            foreach ($attributes as $key => $value)
            {
                if (in_array($key, $this->encryptBase64))
                {
                    $attributes[$key] = base64_decode($value);
                }
            }
        }

        if(isset($this->encryptAES256))
        {
            foreach ($attributes as $key => $value)
            {
                if (in_array($key, $this->encryptAES256))
                {
                    $attributes[$key] = $this->my_decrypt($value);
                }
            }
        }

        return $attributes;
    }
}