<?php 

class Ps_Payment_Order 
{
	public static function getRandomPaymentNo()
	{
		mt_srand( crc32(microtime(true) ) );
		
		$acceptedNumbers = '0123456789';
        $max = strlen($acceptedNumbers) - 1;
        $random_number = "";
        for ($i=0; $i < 9; $i++)
            $random_number .= $acceptedNumbers{mt_rand(0, $max)};
        return $random_number;
	}
}