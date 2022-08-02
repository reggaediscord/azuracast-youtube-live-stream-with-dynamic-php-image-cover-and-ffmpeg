<?php
set_time_limit(3);
ini_set('max_execution_time', 3);

error_reporting( 0 );
ini_set( 'display_errors', 0 );

// error_reporting( E_ALL );
// ini_set( 'display_errors', 1 );

$ret = ExecuteRequest( 'http://XXX.XXX.XXX.XXX:1223/api/nowplaying/1', [], [], '', false );
$json = json_decode( $ret, true);

$image = imagecreatetruecolor( 1920, 1080 );
imagealphablending( $image, false );
imagesavealpha( $image, true );
$col=imagecolorallocatealpha( $image,255,255,255,127 );
imagefill( $image, 0, 0, $col );

$ageInSeconds = 3600;
$minutes = date('i');
$seconds = date('s');
$black = imagecolorallocate( $image, 0, 0, 0 );
$white = imagecolorallocate( $image, 255, 255, 255 );
$font_path = __DIR__ . '/realtime.ttf';

$overlay = imagecreatefrompng( __DIR__ . '/overlay.png' );
imagecopy( $image, $overlay, 0, 0, 0, 0, 1920, 1080 );

$im = imagecreatefromjpeg( $json['now_playing']['song']['art'] );

$width  = imagesx( $im );
$height = imagesy( $im );

imagecopyresampled( $image, $im, 62, 62, 0, 0, 246, 246, $width, $height );

$font_path =  __DIR__ . '/static/festivo.ttf';

$text = $json['now_playing']['song']['title'];
$text = preg_replace( '/[[:^print:]]/', '', $text );
$text = strlen( $text ) > 55 ? substr( $text,0,55 )."..." : $text;

imagettftext( $image, 35, 0, 364, 138, $white, $font_path, $text );

$text = $json['now_playing']['song']['artist'];
$text = preg_replace( '/[[:^print:]]/', '', $text );

imagettftext( $image, 25, 0, 364, 185, $white, $font_path, $text );

$progress = imagecreatefrompng(__DIR__ . '/static/progress.png');
imagecopyresampled( $image, $progress, 0, 0, 0, 0, round( ( $json['now_playing']['elapsed'] * 1920 ) / $json['now_playing']['duration']), 20, 1, 20 );

imagepng($image,  __DIR__ . '/template.png');

if( file_exists( __DIR__ . '/template.png' ) )
{
	rename( __DIR__ . '/template.png', __DIR__ . '/cover.png' );
}

function GetCurl( )
{
	global $c;

	if( isset( $c ) )
	{
		return $c;
	}

	$c = curl_init( );

	curl_setopt_array( $c, [
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING       => '',
		CURLOPT_TIMEOUT        => 30,
		CURLOPT_CONNECTTIMEOUT => 10,
		CURLOPT_HEADER         => true,
		CURLOPT_COOKIESESSION  => true,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_AUTOREFERER    => true,
		CURLOPT_SSL_VERIFYHOST => false,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_USERAGENT => '',
	] );

	if ( !empty( $_SERVER[ 'LOCAL_ADDRESS' ] ) )
	{
		curl_setopt( $c, CURLOPT_INTERFACE, $_SERVER[ 'LOCAL_ADDRESS' ] );
	}

	if( defined( 'CURL_HTTP_VERSION_2_0' ) )
	{
		curl_setopt( $c, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0 );
	}

	return $c;
}

function ExecuteRequest( $URL, $Data = [], $Header = [], $Port = '', $AllowCookie = true )
{
	global $cookie;
	
	$c = GetCurl( );

	curl_setopt( $c, CURLOPT_URL, $URL );
	
	$Keep_Alive = array ( 'Connection: Keep-Alive', 'Keep-Alive: timeout=300' );
	$Header = array_merge( $Keep_Alive, $Header ); 

	curl_setopt( $c, CURLOPT_HTTPHEADER, $Header );
	curl_setopt( $c, CURLOPT_COOKIE, $cookie );
	curl_setopt( $c, CURLOPT_COOKIEFILE, $cookie );
	curl_setopt( $c, CURLOPT_COOKIEJAR, $cookie );

	if( !empty( $Port ) )
	{
		curl_setopt( $c, CURLOPT_PORT, $Port );
	}
	else
	{
		curl_setopt( $c, CURLOPT_PORT, 0 );
	}
	
	if( !empty( $Data ) )
	{
		curl_setopt( $c, CURLOPT_POST, 1 );
		curl_setopt( $c, CURLOPT_POSTFIELDS, $Data );
	}
	else
	{
		curl_setopt( $c, CURLOPT_HTTPGET, 1 );
	}
	
	$retry = 1;
	
	do
	{
		$failed = 0;
	
		$Data = curl_exec( $c );

		$responseCode = curl_getinfo( $c, CURLINFO_HTTP_CODE );
		$HeaderSize = curl_getinfo( $c, CURLINFO_HEADER_SIZE );
		
		$Header = substr( $Data, 0, $HeaderSize );
		$Data = substr( $Data, $HeaderSize );
		
		if( $AllowCookie == 'true' )
		{
			preg_match_all( '/^Set-Cookie:\s*([^;]*)/mi', $Header, $out );
			
			foreach( $out[1] as $item )
			{
				$cookie .= $item . ';';
			}
		}
		
		if( curl_errno ( $c ) )
		{
			$failed = 1;
		}

		if ( $responseCode >= 400 )
		{
			$failed = 1;
		}
		
		$retry--;
		usleep( 300000 );
	}
	while( $retry >= 1 && $failed === 1 && sleep( 1 ) === 0 );

	return $Data;
}
