<?php

namespace deemru;

class ABCode
{
    /**
     * Creates ABCode instance
     * 
     * @param  string       $abc    Encoding alphabet
     * @param  string|null  $base   Base alphabet (binary if not set)
     */
    public function __construct( $abc, $base = null )
    {
        if( !isset( $base ) )
        {
            $base = str_pad( '', 256 );
            for( $i = 0; $i < 256; $i++ )
                $base[$i] = chr( $i );
        }

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

        if( $i == $n )
            return $z;

        if( $i )
        {
            $data = substr( $data, $i );
            $n -= $i;
        }

        for( $i = 0; $i < $n; $i++ )
            if( !isset( $frommap[ $data[$i] ] ) )
                return false;

        return $z . $this->convert( $data, $n, $fromq, $frommap, $toq, $to );
    }

    private function convert( $data, $n, $fromq, $frommap, $toq, $to )
    {
        if( $fromq === 256 )
        {
            $b = bin2hex( $data );
            $b = gmp_init( $b, 16 );
        }
        else
        {
            $b = gmp_init( $frommap[ $data[0] ] );
            for( $i = 1; $i < $n; $i++ )
                $b = gmp_add( $frommap[ $data[$i] ], gmp_mul( $b, $fromq ) );
        }

        $data = '';
        do
        {
            list( $b, $mod ) = gmp_div_qr( $b, $toq );
            $data .= $to[ gmp_intval( $mod ) ];
        }
        while( gmp_intval( $b ) !== 0 );

        return strrev( $data );
    }
}
