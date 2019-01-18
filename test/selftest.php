<?php

echo "ABCode test:\n";
require __DIR__ . '/../vendor/autoload.php';
use deemru\ABCode;

// Built in base58
$data = ABCode::base58()->encode( 'Hello, world!' );
if( $data !== '72k1xXWG59wUsYv7h2' )
    exit( 1 );

// Convert strings to your alphabet
$abcode = new ABCode( 'my_ABC' );
$data = $abcode->encode( $data );
if( $data !== 'BAAy_Cmm_BA_AC_BCA_A_ymymCCmyyBBABBACCyBm___mA_BAm_yA__' )
    exit( 1 );

// Convert between alphabets
$abcode = new ABCode( 'my_ABC', 'another_ABC-123' );
$data = $abcode->decode( $data );
if( $data !== 'otah2_en3_o22ABhhrroA1eCAC3ronBn3t2-o' )
    exit( 1 );

// Cross test alphabets
$ABCs = [
    '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz',
    '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz',
    '0123456789abcdefghijklmnopqrstuvwxyz',
    '!@#$%^&*(){}[]|":<>\\/,.-=',
    '01',
];

$n = count( $ABCs );

function ezrnd( $size )
{
    static $random_bytes;

    if( !isset( $random_bytes ) )
        $random_bytes = function_exists( 'random_bytes' ) ? true : false;

    if( $random_bytes )
        return random_bytes( $size );
    
    $rnd = '';
    while( $size-- )
        $rnd .= chr( mt_rand( 0, 255 ) );
    return $rnd;
}

$probes = 0;
for( $i = 0; $i < $n - 1; $i++ )
for( $j = $i + 1; $j < $n; $j++ )
{
    $abcode_i = new ABCode( $ABCs[$i] );
    $abcode_j = new ABCode( $ABCs[$j] );
    $abcode_ij = new ABCode( $ABCs[$i], $ABCs[$j] );
    $abcode_ji = new ABCode( $ABCs[$j], $ABCs[$i] );

    $t = microtime( true );
    $probes_ij = 0;
    while( microtime( true ) - $t < 0.337 )
    {
        $strlen = mt_rand( 0, 16 );
        $source = $strlen ? ezrnd( $strlen ) : '';

        $encoded_i = $abcode_i->encode( $source );
        $encoded_j = $abcode_j->encode( $source );
        $encoded_ij = $abcode_ij->encode( $encoded_j );
        $encoded_ji = $abcode_ji->encode( $encoded_i );

        if( $encoded_i !== $encoded_ij || $encoded_j !== $encoded_ji )
        {
            echo 'ERROR';
            exit( 1 );
        }

        $decoded_i = $abcode_i->decode( $encoded_i );
        $decoded_j = $abcode_j->decode( $encoded_j );
        $decoded_ij = $abcode_ij->decode( $encoded_i );
        $decoded_ji = $abcode_ji->decode( $encoded_j );

        if( $decoded_i !== $source || $decoded_j !== $source || 
            $encoded_i !== $decoded_ji || $encoded_j !== $decoded_ij )
        {
            echo 'ERROR (source = ' . bin2hex( $source ) . ')';
            exit( 1 );
        }

        $probes_ij++;
    }

    echo "$probes_ij ";
    $probes += $probes_ij;
}

echo "\nSUCCESS ($probes tests passed)";
sleep( 3 );
