<?php

class LECM_File
{
    protected $_contentTypes = array(
        '.art' => 'image/x-jg',
        '.bmp' => 'image/bmp',
        '.bmp_' => 'image/x-windows-bmp',
        '.dwg' => 'image/vnd.dwg',
        '.dwg_' => 'image/x-dwg',
        '.fif' => 'image/fif',
        '.flo' => 'image/florian',
        '.fpx' => 'image/vnd.fpx',
        '.fpx_' => 'image/vnd.net-fpx',
        '.g3' => 'image/g3fax',
        '.gif' => 'image/gif',
        '.ico' => 'image/x-icon',
        '.ief' => 'image/ief',
        '.jpg' => 'image/jpeg',
        '.jpeg' => 'image/pjpeg',
        '.jps' => 'image/x-jps',
        '.jut' => 'image/jutvision',
        '.mcf' => 'image/vasa',
        '.naplps' => 'image/naplps',
        '.niff' => 'image/x-niff',
        '.pbm' => 'image/x-portable-bitmap',
        '.pct' => 'image/x-pict',
        '.pcx' => 'image/x-pcx',
        '.pgm' => 'image/x-portable-graymap',
        '.pgm_' => 'image/x-portable-greymap',
        '.pict' => 'image/pict',
        '.xpm' => 'image/x-xpixmap',
        '.png' => 'image/png',
        '.pnm' => 'image/x-portable-anymap',
        '.ppm' => 'image/x-portable-pixmap',
        '.qif' => 'image/x-quicktime',
        '.rast' => 'image/cmu-raster',
        '.ras' => 'image/x-cmu-raster',
        '.rf' => 'image/vnd.rn-realflash',
        '.rgb' => 'image/x-rgb',
        '.rp' => 'image/vnd.rn-realpix',
        '.tif' => 'image/tiff',
        '.tiff' => 'image/x-tiff',
        '.wbmp' => 'image/vnd.wap.wbmp',
        '.xbm' => 'image/x-xbitmap',
        '.xbm_' => 'image/x-xbm',
        '.xbm__' => 'image/xbm',
        '.xif' => 'image/vnd.xiff',
        '.xpm_' => 'image/xpm',
        '.xwd' => 'image/x-xwd',
        '.xwd_' => 'image/x-xwindowdump',
        '.abc' => 'text/vnd.abc',
        '.htm' => 'text/html',
        '.aip' => 'text/x-audiosoft-intra',
        '.asm' => 'text/x-asm',
        '.asp' => 'text/asp',
        '.c' => 'text/x-c',
        '.txt' => 'text/plain',
        '.csh' => 'text/x-script.csh',
        '.css' => 'text/css',
        '.el' => 'text/x-script.elisp',
        '.etx' => 'text/x-setext',
        '.for' => 'text/x-fortran',
        '.flx' => 'text/vnd.fmi.flexstor',
        '.h' => 'text/x-h',
        '.hlb' => 'text/x-script',
        '.htc' => 'text/x-component',
        '.htt' => 'text/webviewhtml',
        '.ksh' => 'text/x-script.ksh',
        '.js' => 'text/javascript',
        '.js_' => 'text/ecmascript',
        '.java' => 'text/x-java-source',
        '.lsp' => 'text/x-script.lisp',
        '.lsx' => 'text/x-la-asf',
        '.m' => 'text/x-m',
        '.mcf_' => 'text/mcf',
        '.p' => 'text/x-pascal',
        '.pas' => 'text/pascal',
        '.pl' => 'text/x-script.perl',
        '.pm' => 'text/x-script.perl-module',
        '.py' => 'text/x-script.phyton',
        '.rexx' => 'text/x-script.rexx',
        '.rtx' => 'text/richtext',
        '.rt' => 'text/vnd.rn-realtext',
        '.scm' => 'text/x-script.guile',
        '.scm_' => 'text/x-script.scheme',
        '.sgm' => 'text/sgml',
        '.sgml' => 'text/x-sgml',
        '.sh' => 'text/x-script.sh',
        '.shtml' => 'text/x-server-parsed-html',
        '.spc' => 'text/x-speech',
        '.tcl' => 'text/x-script.tcl',
        '.tcsh' => 'text/x-script.tcsh',
        '.tsv' => 'text/tab-separated-values',
        '.uil' => 'text/x-uil',
        '.uri' => 'text/uri-list',
        '.uu' => 'text/x-uuencode',
        '.vcs' => 'text/x-vcalendar',
        '.wml' => 'text/vnd.wap.wml',
        '.wmls' => 'text/vnd.wap.wmlscript',
        '.wsc' => 'text/scriplet',
        '.xml' => 'text/xml',
        '.zsh' => 'text/x-script.zsh',
        '.aif' => 'audio/aiff',
        '.aiff' => 'audio/x-aiff',
        '.au' => 'audio/basic',
        '.au_' => 'audio/x-au',
        '.funk' => 'audio/make',
        '.gsm' => 'audio/x-gsm',
        '.it' => 'audio/it',
        '.jam' => 'audio/x-jam',
        '.midi' => 'audio/midi',
        '.la' => 'audio/nspaudio',
        '.lma' => 'audio/x-nspaudio',
        '.lam' => 'audio/x-liveaudio',
        '.mp2' => 'audio/mpeg',
        '.m3u' => 'audio/x-mpequrl',
        '.mid' => 'audio/x-mid',
        '.midi_' => 'audio/x-midi',
        '.mjf' => 'audio/x-vnd.audioexplosion.mjuicemediafile',
        '.mod' => 'audio/mod',
        '.mod_' => 'audio/x-mod',
        '.mp2_' => 'audio/x-mpeg',
        '.mp3' => 'audio/mpeg3',
        '.mp3_' => 'audio/x-mpeg-3',
        '.pfunk' => 'audio/make.my.funk',
        '.qcp' => 'audio/vnd.qcelp',
        '.rmp' => 'audio/x-pn-realaudio',
        '.ra' => 'audio/x-pn-realaudio-plugin',
        '.ra_' => 'audio/x-realaudio',
        '.rmi' => 'audio/mid',
        '.s3m' => 'audio/s3m',
        '.sid' => 'audio/x-psid',
        '.snd' => 'audio/x-adpcm',
        '.tsi' => 'audio/tsp-audio',
        '.tsp' => 'audio/tsplayer',
        '.voc' => 'audio/voc',
        '.voc_' => 'audio/x-voc',
        '.vox' => 'audio/voxware',
        '.vql' => 'audio/x-twinvq-plugin',
        '.vqf' => 'audio/x-twinvq',
        '.wav' => 'audio/wav',
        '.wav_' => 'audio/x-wav',
        '.xm' => 'audio/xm',
        '.afl' => 'video/animaflex',
        '.asf' => 'video/x-ms-asf',
        '.asx' => 'video/x-ms-asf-plugin',
        '.avi' => 'video/avi',
        '.avi_' => 'video/msvideo',
        '.avi__' => 'video/x-msvideo',
        '.avs' => 'video/avs-video',
        '.dv' => 'video/x-dv',
        '.dl' => 'video/dl',
        '.dl_' => 'video/x-dl',
        '.fli' => 'video/fli',
        '.fli_' => 'video/x-fli',
        '.fmf' => 'video/x-atomic3d-feature',
        '.gl' => 'video/gl',
        '.gl_' => 'video/x-gl',
        '.isu' => 'video/x-isvideo',
        '.m1v' => 'video/mpeg',
        '.mp3__' => 'video/mpeg',
        '.mjpg' => 'video/x-motion-jpeg',
        '.qt' => 'video/quicktime',
        '.movie' => 'video/x-sgi-movie',
        '.mp3___' => 'video/x-mpeg',
        '.mp2__' => 'video/x-mpeq2a',
        '.qtc' => 'video/x-qtc',
        '.rv' => 'video/vnd.rn-realvideo',
        '.scm__' => 'video/x-scm',
        '.vdo' => 'video/vdo',
        '.viv' => 'video/vivo',
        '.vivo' => 'video/vnd.vivo',
        '.vos' => 'video/vosaic',
        '.xdr' => 'video/x-amt-demorun',
        '.xsr' => 'video/x-amt-showrun',
        '' => 'application/octet-stream',
        '.aab' => 'application/x-authorware-bin',
        '.aam' => 'application/x-authorware-map',
        '.aas' => 'application/x-authorware-seg',
        '.ps' => 'application/postscript',
        '.aim' => 'application/x-aim',
        '.ani' => 'application/x-navi-animation',
        '.aos' => 'application/x-nokia-9000-communicator-add-on-software',
        '.aps' => 'application/mime',
        '.arj' => 'application/arj',
        '.asx_' => 'application/x-mplayer2',
        '.avi___' => 'application/x-troff-msvideo',
        '.bcpio' => 'application/x-bcpio',
        '.bin' => 'application/mac-binary',
        '.bin_' => 'application/macbinary',
        '.bin__' => 'application/x-binary',
        '.bin___' => 'application/x-macbinary',
        '.book' => 'application/book',
        '.bz2' => 'application/x-bzip2',
        '.bsh' => 'application/x-bsh',
        '.bz' => 'application/x-bzip',
        '.cat' => 'application/vnd.ms-pki.seccat',
        '.ccad' => 'application/clariscad',
        '.cco' => 'application/x-cocoa',
        '.cdf' => 'application/cdf',
        '.cdf_' => 'application/x-cdf',
        '.nc' => 'application/x-netcdf',
        '.cer' => 'application/pkix-cert',
        '.crt' => 'application/x-x509-ca-cert',
        '.chat' => 'application/x-chat',
        '.class' => 'application/java',
        '.class_' => 'application/java-byte-code',
        '.class__' => 'application/x-java-class',
        '.cpio' => 'application/x-cpio',
        '.cpt' => 'application/mac-compactpro',
        '.cpt_' => 'application/x-compactpro',
        '.cpt__' => 'application/x-cpt',
        '.crl' => 'application/pkcs-crl',
        '.crl_' => 'application/pkix-crl',
        '.crt_' => 'application/x-x509-user-cert',
        '.csh_' => 'application/x-csh',
        '.css_' => 'application/x-pointplus',
        '.dir' => 'application/x-director',
        '.deepv' => 'application/x-deepv',
        '.doc' => 'application/msword',
        '.dp' => 'application/commonground',
        '.drw' => 'application/drafting',
        '.dvi' => 'application/x-dvi',
        '.dwg__' => 'application/acad',
        '.dxf' => 'application/dxf',
        '.elc' => 'application/x-bytecode.elisp',
        '.elc_' => 'application/x-elc',
        '.env' => 'application/x-envoy',
        '.es' => 'application/x-esrehber',
        '.evy' => 'application/envoy',
        '.fdf' => 'application/vnd.fdf',
        '.fif_' => 'application/fractals',
        '.frl' => 'application/freeloader',
        '.gsp' => 'application/x-gsp',
        '.gss' => 'application/x-gss',
        '.gtar' => 'application/x-gtar',
        '.gz' => 'application/x-compressed',
        '.zip' => 'application/x-compressed',
        '.tgz' => 'application/x-compressed',
        '.gzip' => 'application/x-gzip',
        '.hdf' => 'application/x-hdf',
        '.help' => 'application/x-helpfile',
        '.hgl' => 'application/vnd.hp-hpgl',
        '.hlp' => 'application/hlp',
        '.hlp_' => 'application/x-winhelp',
        '.hqx' => 'application/binhex',
        '.hqx_' => 'application/binhex4',
        '.hqx__' => 'application/mac-binhex',
        '.hqx___' => 'application/mac-binhex40',
        '.hqx____' => 'application/x-binhex40',
        '.hqx_____' => 'application/x-mac-binhex40',
        '.hta' => 'application/hta',
        '.iges' => 'application/iges',
        '.ima' => 'application/x-ima',
        '.imap' => 'application/x-httpd-imap',
        '.inf' => 'application/inf',
        '.ins' => 'application/x-internett-signup',
        '.ip' => 'application/x-ip2',
        '.iv' => 'application/x-inventor',
        '.ivy' => 'application/x-livescreen',
        '.jcm' => 'application/x-java-commerce',
        '.js__' => 'application/x-javascript',
        '.js___' => 'application/javascript',
        '.js____' => 'application/ecmascript',
        '.ksh_' => 'application/x-ksh',
        '.latex' => 'application/x-latex',
        '.lha' => 'application/lha',
        '.lha_' => 'application/x-lha',
        '.lsp_' => 'application/x-lisp',
        '.lzh' => 'application/x-lzh',
        '.lzx' => 'application/lzx',
        '.lzx_' => 'application/x-lzx',
        '.man' => 'application/x-troff-man',
        '.map' => 'application/x-navimap',
        '.mbd' => 'application/mbedlet',
        '.mc' => 'application/x-magic-cap-package-1.0',
        '.mcd' => 'application/mcad',
        '.mcd_' => 'application/x-mathcad',
        '.mcp' => 'application/netmc',
        '.me' => 'application/x-troff-me',
        '.mif' => 'application/x-frame',
        '.mif_' => 'application/x-mif',
        '.mme' => 'application/base64',
        '.mm' => 'application/x-meme',
        '.mpc' => 'application/x-project',
        '.mpp' => 'application/vnd.ms-project',
        '.mrc' => 'application/marc',
        '.ms' => 'application/x-troff-ms',
        '.mzz' => 'application/x-vnd.audioexplosion.mzz',
        '.ncm' => 'application/vnd.nokia.configuration-message',
        '.nix' => 'application/x-mix-transfer',
        '.nsc' => 'application/x-conference',
        '.nvd' => 'application/x-navidoc',
        '.oda' => 'application/oda',
        '.omc' => 'application/x-omc',
        '.omcd' => 'application/x-omcdatamaker',
        '.omcr' => 'application/x-omcregerator',
        '.p10' => 'application/pkcs10',
        '.p10_' => 'application/x-pkcs10',
        '.p12' => 'application/pkcs-12',
        '.p12_' => 'application/x-pkcs12',
        '.p7a' => 'application/x-pkcs7-signature',
        '.p7m' => 'application/pkcs7-mime',
        '.p7c' => 'application/x-pkcs7-mime',
        '.p7r' => 'application/x-pkcs7-certreqresp',
        '.p7s' => 'application/pkcs7-signature',
        '.prt' => 'application/pro_eng',
        '.pcl' => 'application/vnd.hp-pcl',
        '.pcl_' => 'application/x-pcl',
        '.pdf' => 'application/pdf',
        '.pkg' => 'application/x-newton-compatible-pkg',
        '.pko' => 'application/vnd.ms-pki.pko',
        '.plx' => 'application/x-pixclscript',
        '.pm4' => 'application/x-pagemaker',
        '.pnm_' => 'application/x-portable-anymap',
        '.ppt' => 'application/mspowerpoint',
        '.pot' => 'application/vnd.ms-powerpoint',
        '.ppt_' => 'application/powerpoint',
        '.ppt__' => 'application/x-mspowerpoint',
        '.pre' => 'application/x-freelance',
        '.ras_' => 'application/x-cmu-raster',
        '.rm' => 'application/vnd.rn-realmedia',
        '.rng' => 'application/ringing-tones',
        '.rng_' => 'application/vnd.nokia.ringing-tone',
        '.rnx' => 'application/vnd.rn-realplayer',
        '.roff' => 'application/x-troff',
        '.rtf' => 'application/rtf',
        '.rtf_' => 'application/x-rtf',
        '.sbk' => 'application/x-tbook',
        '.scm___' => 'application/x-lotusscreencam',
        '.sdp' => 'application/sdp',
        '.sdp_' => 'application/x-sdp',
        '.sdr' => 'application/sounder',
        '.sea' => 'application/sea',
        '.sea_' => 'application/x-sea',
        '.set' => 'application/set',
        '.sh_' => 'application/x-sh',
        '.shar' => 'application/x-shar',
        '.sit' => 'application/x-sit',
        '.sit_' => 'application/x-stuffit',
        '.sl' => 'application/x-seelogo',
        '.smil' => 'application/smil',
        '.sol' => 'application/solids',
        '.spc_' => 'application/x-pkcs7-certificates',
        '.spl' => 'application/futuresplash',
        '.sprite' => 'application/x-sprite',
        '.wsrc' => 'application/x-wais-source',
        '.ssm' => 'application/streamingmedia',
        '.sst' => 'application/vnd.ms-pki.certstore',
        '.step' => 'application/step',
        '.stl' => 'application/sla',
        '.stl_' => 'application/vnd.ms-pki.stl',
        '.stl__' => 'application/x-navistyle',
        '.sv4cpio' => 'application/x-sv4cpio',
        '.sv4crc' => 'application/x-sv4crc',
        '.wrl' => 'application/x-world',
        '.swf' => 'application/x-shockwave-flash',
        '.tar' => 'application/x-tar',
        '.tbk' => 'application/toolbook',
        '.tcl_' => 'application/x-tcl',
        '.tex' => 'application/x-tex',
        '.texinfo' => 'application/x-texinfo',
        '.text' => 'application/plain',
        '.tgz_' => 'application/gnutar',
        '.tsp_' => 'application/dsptype',
        '.unv' => 'application/i-deas',
        '.ustar' => 'application/x-ustar',
        '.vcd' => 'application/x-cdlink',
        '.vda' => 'application/vda',
        '.vew' => 'application/groupwise',
        '.vmd' => 'application/vocaltec-media-desc',
        '.vmf' => 'application/vocaltec-media-file',
        '.vrml' => 'application/x-vrml',
        '.vsw' => 'application/x-visio',
        '.w60' => 'application/wordperfect6.0',
        '.w61' => 'application/wordperfect6.1',
        '.wb1' => 'application/x-qpro',
        '.web' => 'application/vnd.xara',
        '.wk1' => 'application/x-123',
        '.wmlc' => 'application/vnd.wap.wmlc',
        '.wmlsc' => 'application/vnd.wap.wmlscriptc',
        '.wp' => 'application/wordperfect',
        '.wpd' => 'application/x-wpwin',
        '.wq1' => 'application/x-lotus',
        '.wri' => 'application/mswrite',
        '.wri_' => 'application/x-wri',
        '.wtk' => 'application/x-wintalk',
        '.xls' => 'application/excel',
        '.xls_' => 'application/x-excel',
        '.xls__' => 'application/x-msexcel',
        '.xls___' => 'application/vnd.ms-excel',
        '.xml____' => 'application/xml',
        '.xpix' => 'application/x-vnd.ls-xpix',
        '.zip__' => 'application/x-compress',
        '.zip_' => 'application/zip',
        '.mid_' => 'application/x-midi',
        '.3dmf' => 'x-world/x-3dmf',
        '.mime' => 'message/rfc822',
        '.mime_' => 'www/mime',
        '.pdb' => 'chemical/x-pdb',
        '.pov' => 'model/x-pov',
        '.pvu' => 'paleovu/x-pv',
        '.pyc' => 'applicaiton/x-bytecode.python',
        '.svr' => 'x-world/x-svr',
        '.ustar_' => 'multipart/x-ustar',
        '.vrml_' => 'model/vrml',
        '.wrz' => 'x-world/x-vrml',
        '.vrt' => 'x-world/x-vrt',
        '.xgz' => 'xgl/drawing',
        '.wmf' => 'windows/metafile',
        '.xmz' => 'xgl/movie',
        '.zip___' => 'multipart/x-zip',
        '.dwf' => 'drawing/x-dwf',
        '.dwf_' => 'model/vnd.dwf',
        '.gzip_' => 'multipart/x-gzip',
        '.ice' => 'x-conference/x-cooltalk',
        '.iges_' => 'model/iges',
        '.ivr' => 'i-world/i-vrml',
        '.kar' => 'music/x-karaoke',
        '.mhtml' => 'message/rfc822',
        '.midi__' => 'music/crescendo',
        '.midi___' => 'x-music/x-midi',
    );

    public function getFileTypeByContentType($content_type){
        $file_type = array_search($content_type, $this->_contentTypes);
        $file_type = str_replace('_','',$file_type);
        return $file_type;
    }

    /**
     * TODO: URL
     */

    public function urlExists($url, $codes = null)
    {
        if(!$codes){
            $codes = $this->httpStatusError();
        }
        if(function_exists('get_headers')){
            return $this->checkUrlByHeader($url, $codes);
        } else {
            return $this->checkUrlByCurl($url, $codes);
        }
    }

    public function checkUrlByHeader($url, $codes = null)
    {
        if(!$codes){
            $codes = $this->httpStatusError();
        }
        $result = true;
        $headers = @get_headers($url);
        if(!$headers){
            return false;
        }
        foreach($codes as $code){
            if(strpos($headers[0], $code) !== false){
                $result = false;
            }
        }
        return $result;
    }

    public function checkUrlByCurl($url, $codes = null)
    {
        if(!$codes){
            $codes = $this->httpStatusError();
        }
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_NOBODY, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $result = curl_exec($ch);
        if ($result !== false) {
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (in_array($statusCode, $codes)) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    public function httpStatusError()
    {
        return array(
            '404', '403', '500'
        );
    }

    public function isUrlEncode($path){
        $is_encoded = @preg_match('~%[0-9A-F]{2}~i', $path);
        return $is_encoded;
    }

    public function getRawUrl($url){
        $scheme = parse_url($url, PHP_URL_SCHEME);
        $user = parse_url($url, PHP_URL_USER);
        $pass = parse_url($url, PHP_URL_PASS);
        $host = parse_url($url, PHP_URL_HOST);
        $port = parse_url($url, PHP_URL_PORT);
        $path = parse_url($url, PHP_URL_PATH);
        $query = parse_url($url, PHP_URL_QUERY);
        $fragment = parse_url($url, PHP_URL_FRAGMENT);
        $raw_url = '';
        if($scheme) $raw_url .= $scheme.'://';
        if($user && $path) $raw_url .= $user.':'.$pass.'@';
        $raw_url .= $host;
        if($port) $raw_url .= ':'.$port;
        if($this->isUrlEncode($path)){
            $raw_url .= $path;
        } else {
            $raw_url .= $this->rawUrlEncode($path);
        }
        if($query) $raw_url .= '?'.$query;
        if($fragment) $raw_url .= '#'.$fragment;
        return $raw_url;
    }

    public function rawUrlEncode($path){
        $splits = explode('/',$path);
        $raw_path = array();
        foreach($splits as $key => $split){
            $raw_path[$key] = rawurlencode($split);
        }
        $raw_path = implode('/', $raw_path);
        return $raw_path;
    }

    public function stripDomainFromUrl($url){
        $path = parse_url($url, PHP_URL_PATH);
        $query = parse_url($url, PHP_URL_QUERY);
        $fragment = parse_url($url, PHP_URL_FRAGMENT);
        if($query) $path .= '?'.$query;
        if($fragment) $path .= '#'.$fragment;
        return $path;
    }

    public function getDomainFromUrl($url){
        $path = $this->stripDomainFromUrl($url);
        $domain = str_replace($path,'',$url);
        return $domain;
    }

    public function joinQueryToFileName($path){
        $base_name = parse_url($path, PHP_URL_PATH);
        $query = parse_url($path, PHP_URL_QUERY);
        if($query) $base_name .= '-'.$query;
        return $base_name;
    }

    public function joinUrlPath($url, $path)
    {
        $full_url = rtrim($url, '/');
        if($path){
            $full_url .= '/' . ltrim($path, '/');
        }
        return $full_url;
    }

    public function getFileNameFromVirtualUrl($url, $path = '', $join_query =  true){
        $fileName = null;
        $headers = get_headers($url, 1);
        $headers = array_combine(array_map("strtolower",array_keys($headers)),$headers);
        if(isset($headers['content-disposition'])){
            $fileName = strstr($headers['content-disposition'], "=");
            $fileName = trim($fileName,"=\"'");
        }
        if(!$fileName){
            $extension = $this->getFileTypeByContentType($headers['content-type']);
            if(!$path) $path = $this->stripDomainFromUrl($url);
            $fileName = parse_url($path, PHP_URL_PATH);
            if($join_query){
                $fileName = $this->joinQueryToFileName($path);
            }
            $fileName .= $extension;
        }
        return $fileName;
    }

    public function isVirtualUrl($url, $path = '')
    {
        $result = false;
        $full_url = $url;
        if($path){
            $full_url = $this->joinUrlPath($url, $path);
        }
        $query = parse_url($full_url, PHP_URL_QUERY);
        if($query){
            $result = true;
        }
        return $result;
    }

    /**
     * TODO: DOWNLOAD
     */

    public function downloadByCurl($url, $path){
        $this->_createDirName($path);
        $fp = @fopen($path, 'wb');
        if(!$fp){
            return false;
        }
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        $data = curl_exec($ch);
        curl_close($ch);
        @flush($fp);
        @fclose($fp);
        if($data){
            return true;
        }else{
            $this->_deleteFile($path);
        }
        return false;
    }

    public function downloadFile($domain, $path, $download_dir = '', $base_name = false, $override = false, $rename = false, $dispersion = false){
        $url = rtrim($domain,'/').'/'.ltrim($path);
        $url = $this->getRawUrl($url);
        if($base_name){
            $path = pathinfo($path, PATHINFO_BASENAME);
        }
        if(parse_url($path, PHP_URL_QUERY)){
            $download_path = $this->getFileNameFromVirtualUrl($url, $path);
        } else{
            $download_path = $path;
        }
        $download_path = $this->changeSpecialCharInPath($download_path);
        if($dispersion) $download_path = $this->createFileDispersion($download_path);
        $download_path_src = rtrim($download_dir).'/'.ltrim($download_path);
        if(!$override){
            if(file_exists($download_path_src) && !$rename){
                return false;
            }
            $download_path = $this->renameFileIfExists($download_path, $download_dir);
            $download_path_src = rtrim($download_dir).'/'.ltrim($download_path);
        }
        $result = $this->downloadByCurl($url, $download_path_src);
        if($result){
            return $download_path;
        }
        return false;
    }

    public function downloadFileFromUrl($url, $download_dir = '', $base_name = false, $override = false, $rename = false, $dispersion = false){
        $path = $this->stripDomainFromUrl($url);
        $domain = $this->getDomainFromUrl($url);
        return $this->downloadFile($domain, $path, $download_dir, $base_name, $override, $rename, $dispersion);
    }

    /**
     * TODO: FILE
     */
    public function createFileSuffix($file_path, $suffix, $character = '_'){
        $new_path = '';
        $dir_name = pathinfo($file_path, PATHINFO_DIRNAME);
        $file_name = pathinfo($file_path, PATHINFO_FILENAME);
        $file_ext = pathinfo($file_path , PATHINFO_EXTENSION);
        if($dir_name && $dir_name != '.') $new_path .= $dir_name.'/';
        $new_path .= $file_name.$character.$suffix.'.'.$file_ext;
        return $new_path;
    }

    public function renameFileIfExists($file_name, $path = false, $base_name = false, $dispersion = false){
        if($path && is_string($path)){
            $path = rtrim($path,'/').'/';
        } else {
            $path = '';
        }
        $file_name =ltrim($file_name,'/');
        if($dispersion){
            $file_name = $this->createFileDispersion($file_name);
        } else {
            if($base_name){
                $file_name = pathinfo($file_name, PATHINFO_BASENAME);
            }
        }
        $new_name= $file_name;
        $file_path = $path.$new_name;
        $i = 1;
        while(file_exists($file_path)){
            $new_name = $this->createFileSuffix($file_name, $i);
            $file_path = $path.$new_name;
            $i++;
        }
        return $new_name;
    }

    public function changeSpecialCharInPath($path, $character = '-'){
        $splits = explode('/',$path);
        $data = array();
        foreach($splits as $key => $split){
            $split = preg_replace('/[^A-Za-z0-9.\-_]/', $character, $split);
            $data[$key] = $split;
        }
        $path = implode('/',$data);
        return $path;
    }

    public function createFileDispersion($path, $num_char = 2, $character = '_'){
        $base_name = pathinfo($path, PATHINFO_BASENAME);
        $char = 0;
        $dispersion_path = '';
        while (($char < $num_char) && ($char < strlen($base_name))) {
            if (empty($dispersion_path)) {
                $dispersion_path .= ('.' == $base_name[$char]) ? $character : $base_name[$char];
            } else {
                $dispersion_path .= '/';
                $dispersion_path .= ('.' == $base_name[$char]) ? $character : $base_name[$char];
            }
            $char ++;
        }
        return $dispersion_path.'/'.$base_name;
    }

    protected function _createDirName($path, $mod = 777, $recursive = true){
        $dir_name = dirname($path);
        if(!is_dir($dir_name)){
            @mkdir($dir_name, $mod, $recursive);
        }
    }

    protected function _deleteFile($path){
        if(file_exists($path)){
            @unlink($path);
        }
    }

    /**
     * TODO: UPLOAD
     */

    public function upload($file, $desc, $name = null, $allowExt = null, $override = false, $rename = false, $dispersion = false)
    {
        if($file['error']){
            return false;
        }
        $pathInfo = pathinfo($file['name']);
        $extension = $pathInfo['extension'];
        if($allowExt && !in_array($extension, $allowExt)){
            return false;
        }
        $name_upload = '';
        if(!$name){
            $name_upload = basename($file['name']);
            $name_upload = $name_upload . '.'. $extension;;
        } else {
            $name_upload = $name;
        }
        if($dispersion){
            $name_upload = $this->createFileDispersion($name_upload);
        }
        $path_upload = rtrim($desc, '\\/');
        $path_upload .= '/' . ltrim($name_upload, '\\/');
        if(file_exists($path_upload)){
            if($override){
                @unlink($path_upload);
            } else {
                if($rename){
                    $name_upload = $this->renameFileIfExists($name_upload, $desc);
                } else {
                    return false;
                }
            }
        }
        $path_upload = rtrim($desc, '\\/');
        $path_upload .= '/' . ltrim($name_upload, '\\/');
        $upload = move_uploaded_file($file['tmp_name'], $path_upload);
        if(!$upload){
            return false;
        }
        return $name_upload;
    }
}
