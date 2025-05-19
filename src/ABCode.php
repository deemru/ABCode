<?php

namespace deemru;

class ABCode
{
    private $a;
    private $aq;
    private $amap;
    private $b;
    private $bq;
    private $bmap;

    /**
     * Creates ABCode instance
     * 
     * @param  string       $abc    Encoding alphabet
     * @param  string|null  $base   Base alphabet (binary if not set)
     */
    public function __construct( $abc, $base = null )
    {
        if( !isset( $base ) )
            $base = implode( '', array_map( 'chr', range( 0, 255 ) ) );

        $this->a = $abc;
        $this->aq = strlen( $this->a );
        $this->amap = $this->map( $abc );
        $this->b = $base;
        $this->bq = strlen( $this->b );
        $this->bmap = $this->map( $base );
    }

    /**
     * Returns static instance of ABCode with bitcoin base58 encoding alphabet
     *
     * @return ABCode
     */
    static public function base58()
    {
        static $base58;

        if( !isset( $base58 ) )
            $base58 = new ABCode( '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz' );

        return $base58;
    }

    /**
     * Encodes data from the base alphabet to the encoding alphabet
     *
     * @param  string $data
     *
     * @return string|false Encoded data or FALSE on failure
     */
    public function encode( $data )
    {
        return $this->abcode( $data, $this->b, $this->bq, $this->bmap, $this->a, $this->aq );
    }

    /**
     * Decodes data from the encoding alphabet to the base alphabet
     *
     * @param  string $data
     *
     * @return string|false Decoded data or FALSE on failure
     */
    public function decode( $data )
    {
        return $this->abcode( $data, $this->a, $this->aq, $this->amap, $this->b, $this->bq );
    }

    private function map( $abc )
    {
        $map = array();
        for( $i = 0, $n = strlen( $abc ); $i < $n; $i++ )
            $map[ $abc[$i] ] = $i;
        return $map;
    }

    private function abcode( $data, $from, $fromq, $frommap, $to, $toq )
    {
        $n = strlen( $data );
        $z = '';
        for( $i = 0; $i < $n; $i++ )
            if( $data[$i] === $from[0] )
                $z .= $to[0];
            else
                break;

        if( $i === $n )
            return $z;

        if( $i )
        {
            $data = substr( $data, $i );
            $n -= $i;
        }

        if( $fromq !== 256 )
        for( $i = 0; $i < $n; $i++ )
            if( !isset( $frommap[ $data[$i] ] ) )
                return false;

        return $z . $this->convert( $data, $n, $fromq, $frommap, $toq, $to );
    }

    private function convert( $data, $n, $fromq, $frommap, $toq, $to )
    {
        if( $fromq === 256 )
        {
            $b = gmp_init( bin2hex( $data ), 16 );
        }
        else
        {
            $max = (int)( PHP_INT_MAX / $fromq ) - 1;
            $t = $frommap[ $data[0] ];
            $tq = $fromq;
            for( $i = 1; $i < $n; $i++ )
            {
                $t = $t * $fromq + $frommap[ $data[$i] ];
                $tq *= $fromq;

                if( $tq > $max )
                {
                    $b = isset( $b ) ? gmp_add( gmp_mul( $b, $tq ), $t ) : gmp_init( $t );

                    if( ++$i === $n )
                    {
                        $tq = 1;
                        break;
                    }

                    $t = $frommap[ $data[$i] ];
                    $tq = $fromq;
                }
            }

            if( $tq !== 1 )
                $b = isset( $b ) ? gmp_add( gmp_mul( $b, $tq ), $t ) : gmp_init( $t );
        }

        if( $toq === 256 )
        {
            $data = gmp_strval( $b, 16 );
            if( ( strlen( $data ) & 1 ) !== 0 )
                return hex2bin( '0' . $data );
            return hex2bin( $data );
        }

        $data = '';
        do
        {
            list( $b, $mod ) = gmp_div_qr( $b, $toq );
            $data .= $to[ gmp_intval( $mod ) ];
        }
        while( gmp_sign( $b ) !== 0 );

        return strrev( $data );
    }
}
