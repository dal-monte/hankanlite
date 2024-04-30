<?php

class Token
{
    public function Token($beforeToken = null, $afterToken = null)
    {
        $nullTokenBool = is_null($beforeToken) && is_null($afterToken);
        $existTokenBool = !is_null($beforeToken) && !is_null($afterToken);

        if ($nullTokenBool) {
            $byteToken = random_bytes(16);
            $stringToken = bin2hex($byteToken);
            return $stringToken;
        } elseif ($existTokenBool) {
            $boolToken = false;
            if ($beforeToken === $afterToken) {
                $boolToken = true;
            }
            return $boolToken;
        }
    }
}
