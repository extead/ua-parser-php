<?php

namespace Extead\UAParser;

/**
 * Class UAParser
 * @package Extead\UAParser
 */
class UAParser
{
    const LIBVERSION = '0.7.14';
    const _EMPTY = '';
    const UNKNOWN = '?';
    const FUNC_TYPE = 'function';
    const UNDEF_TYPE = 'undefined';
    const OBJ_TYPE = 'object';
    const STR_TYPE = 'string';
    const MAJOR = 'major'; // deprecated
    const MODEL = 'model';
    const NAME = 'name';
    const TYPE = 'type';
    const VENDOR = 'vendor';
    const VERSION = 'version';
    const ARCHITECTURE = 'architecture';
    const CONSOLE = 'console';
    const MOBILE = 'mobile';
    const TABLET = 'tablet';
    const SMARTTV = 'smarttv';
    const WEARABLE = 'wearable';
    const EMBEDDED = 'embedded';

    private $ua;
    private $rgxmap = [];

    private $maps = [

        'browser' => [
            'oldsafari' => [
                'version' => [
                    '1.0' => '/8',
                    '1.2' => '/1',
                    '1.3' => '/3',
                    '2.0' => '/412',
                    '2.0.2' => '/416',
                    '2.0.3' => '/417',
                    '2.0.4' => '/419',
                    '?' => '/'
                ]
            ]
        ],

        'device' => [
            'amazon' => [
                'model' => [
                    'Fire Phone' => ['SD', 'KF']
                ]
            ],
            'sprint' => [
                'model' => [
                    'Evo Shift 4G' => '7373KT'
                ],
                'vendor' => [
                    'HTC' => 'APA',
                    'Sprint' => 'Sprint'
                ]
            ]
        ],

        'os' => [
            'windows' => [
                'version' => [
                    'ME' => '4.90',
                    'NT 3.11' => 'NT3.51',
                    'NT 4.0' => 'NT4.0',
                    '2000' => 'NT 5.0',
                    'XP' => ['NT 5.1', 'NT 5.2'],
                    'Vista' => 'NT 6.0',
                    '7' => 'NT 6.1',
                    '8' => 'NT 6.2',
                    '8.1' => 'NT 6.3',
                    '10' => ['NT 6.4', 'NT 10.0'],
                    'RT' => 'ARM'
                ]
            ]
        ]

    ];

    private $mapper;
    private $util;

    public function __construct($uastring = null, $extensions = null)
    {

        $this->mapper = new Mapper();
        $this->util = new Util();

        $this->rgxmap = [
            'browser' => [
                [

                // Presto based
                '/(opera\smini)\/([\w\.-]+)/i',                                       // Opera Mini
                '/(opera\s[mobiletab]+).+version\/([\w\.-]+)/i',                      // Opera Mobi/Tablet
                '/(opera).+version\/([\w\.]+)/i',                                     // Opera > 9.80
                '/(opera)[\/\s]+([\w\.]+)/i'                                          // Opera < 9.80
            ], [self::NAME, self::VERSION], [

                '/(opios)[\/\s]+([\w\.]+)/i'                                          // Opera mini on iphone >= 8.0
            ], [[self::NAME, 'Opera Mini'], self::VERSION], [

                '/\s(opr)\/([\w\.]+)/i'                                               // Opera Webkit
            ], [[self::NAME, 'Opera'], self::VERSION], [

                // Mixed
                '/(kindle)\/([\w\.]+)/i',                                             // Kindle
                '/(lunascape|maxthon|netfront|jasmine|blazer)[\/\s]?([\w\.]+)*/i',
                // Lunascape/Maxthon/Netfront/Jasmine/Blazer

                // Trident based
                '/(avant\s|iemobile|slim|baidu)(?:browser)?[\/\s]?([\w\.]*)/i',
                // Avant/I'EMobile/SlimBrowser/Baidu
                '/(?:ms|\()(ie)\s([\w\.]+)/i',                                        // Internet Explorer

                // Webkit/KHTML based
                '/(rekonq)\/([\w\.]+)*/i',                                            // Rekonq
                '/(chromium|flock|rockmelt|midori|epiphany|silk|skyfire|ovibrowser|bolt|iron|vivaldi|iridium|phantomjs|bowser)\/([\w\.-]+)/i'
                // Chromium/Flock/RockMelt/Midori/Epiphany/Silk/Skyfire/Bolt/Iron/I'ridium/PhantomJS/Bowser
            ], [self::NAME, self::VERSION], [

                '/(trident).+rv[:\s]([\w\.]+).+like\sgecko/i'                         // IE11
            ], [[self::NAME, 'IE'], self::VERSION], [

                '/(edge)\/((\d+)?[\w\.]+)/i'                                          // Microsoft Edge
            ], [self::NAME, self::VERSION], [

                '/(yabrowser)\/([\w\.]+)/i'                                           // Yandex
            ], [[self::NAME, 'Yandex'], self::VERSION], [

                '/(puffin)\/([\w\.]+)/i'                                              // Puffin
            ], [[self::NAME, 'Puffin'], self::VERSION], [

                '/((?:[\s\/])uc?\s?browser|(?:juc.+)ucweb)[\/\s]?([\w\.]+)/i'
                // UCBrowser
            ], [[self::NAME, 'UCBrowser'], self::VERSION], [

                '/(comodo_dragon)\/([\w\.]+)/i'                                       // Comodo Dragon
            ], [[self::NAME, '/_/', ' '], self::VERSION], [

                '/(micromessenger)\/([\w\.]+)/i'                                      // WeChat
            ], [[self::NAME, 'WeChat'], self::VERSION], [

                '/(QQ)\/([\d\.]+)/i'                                                  // QQ, aka ShouQ
            ], [self::NAME, self::VERSION], [

                '/m?(qqbrowser)[\/\s]?([\w\.]+)/i'                                    // QQBrowser
            ], [self::NAME, self::VERSION], [

                '/xiaomi\/miuibrowser\/([\w\.]+)/i'                                   // MIUI Browser
            ], [self::VERSION, [self::NAME, 'MIUI Browser']], [

                '/;fbav\/([\w\.]+);/i'                                                // Facebook App for iOS & Android
            ], [self::VERSION, [self::NAME, 'Facebook']], [

                '/(headlesschrome) ([\w\.]+)/i'                                       // Chrome Headless
            ], [self::VERSION, [self::NAME, 'Chrome Headless']], [

                '/\swv\).+(chrome)\/([\w\.]+)/i'                                      // Chrome WebView
            ], [[self::NAME, '/(.+)/', '$1 WebView'], self::VERSION], [

                '/((?:oculus|samsung)browser)\/([\w\.]+)/i'
            ], [[self::NAME, '/(.+(?:g|us))(.+)/', '$1 $2'], self::VERSION], [                // Oculus / Samsung Browser

                '/android.+version\/([\w\.]+)\s+(?:mobile\s?safari|safari)*/i'        // Android Browser
            ], [self::VERSION, [self::NAME, 'Android Browser']], [

                '/(chrome|omniweb|arora|[tizenoka]{5}\s?browser)\/v?([\w\.]+)/i'
                // Chrome/OmniWeb/Arora/Tizen/Nokia
            ], [self::NAME, self::VERSION], [

                '/(dolfin)\/([\w\.]+)/i'                                              // Dolphin
            ], [[self::NAME, 'Dolphin'], self::VERSION], [

                '/((?:android.+)crmo|crios)\/([\w\.]+)/i'                             // Chrome for Android iOS
            ], [[self::NAME, 'Chrome'], self::VERSION], [

                '/(coast)\/([\w\.]+)/i'                                               // Opera Coast
            ], [[self::NAME, 'Opera Coast'], self::VERSION], [

                '/fxios\/([\w\.-]+)/i'                                                // Firefox for iOS
            ], [self::VERSION, [self::NAME, 'Firefox']], [

                '/version\/([\w\.]+).+?mobile\/\w+\s(safari)/i'                       // Mobile Safari
            ], [self::VERSION, [self::NAME, 'Mobile Safari']], [

                '/version\/([\w\.]+).+?(mobile\s?safari|safari)/i'                    // Safari & Safari Mobile
            ], [self::VERSION, self::NAME], [

                '/webkit.+?(mobile\s?safari|safari)(\/[\w\.]+)/i'                     // Safari < 3.0
            ], [self::NAME, [self::VERSION, function ($str, $map) {
                return $this->mapper->str($str, $map);
            }, $this->maps['browser']['oldsafari']['version']]], [

                '/(konqueror)\/([\w\.]+)/i',                                          // Konqueror
                '/(webkit|khtml)\/([\w\.]+)/i'
            ], [self::NAME, self::VERSION], [

                // Gecko based
                '/(navigator|netscape)\/([\w\.-]+)/i'                                 // Netscape
            ], [[self::NAME, 'Netscape'], self::VERSION], [
                '/(swiftfox)/i',                                                      // Swiftfox
                '/(icedragon|iceweasel|camino|chimera|fennec|maemo\sbrowser|minimo|conkeror)[\/\s]?([\w\.\+]+)/i',
                // IceDragon/I'ceweasel/Camino/Chimera/Fennec/Maemo/Minimo/Conkeror
                '/(firefox|seamonkey|k-meleon|icecat|iceape|firebird|phoenix)\/([\w\.-]+)/i',
                // Firefox/SeaMonkey/K-Meleon/IceCat/I'ceApe/Firebird/Phoenix
                '/(mozilla)\/([\w\.]+).+rv\:.+gecko\/\d+/i',                          // Mozilla

                // Other
                '/(polaris|lynx|dillo|icab|doris|amaya|w3m|netsurf|sleipnir)[\/\s]?([\w\.]+)/i',
                // Polaris/Lynx/Dillo/iCab/Doris/Amaya/w3m/NetSurf/Sleipnir
                '/(links)\s\(([\w\.]+)/i',                                            // Links
                '/(gobrowser)\/?([\w\.]+)*/i',                                        // GoBrowser
                '/(ice\s?browser)\/v?([\w\._]+)/i',                                   // ICE Browser
                '/(mosaic)[\/\s]([\w\.]+)/i'                                          // Mosaic
            ], [self::NAME, self::VERSION]

                /* /////////////////////
                // Media players BEGIN
                ////////////////////////
                , [
                '/(apple(?:coremedia|))\/((\d+)[\w\._]+)/i',                          // Generic Apple CoreMedia
                '/(coremedia) v((\d+)[\w\._]+)/i'
                ], [self::NAME, self::VERSION], [
                '/(aqualung|lyssna|bsplayer)\/((\d+)?[\w\.-]+)/i'                     // Aqualung/Lyssna/BSPlayer
                ], [self::NAME, self::VERSION], [
                '/(ares|ossproxy)\s((\d+)[\w\.-]+)/i'                                 // Ares/OSSProxy
                ], [self::NAME, self::VERSION], [
                '/(audacious|audimusicstream|amarok|bass|core|dalvik|gnomemplayer|music on console|nsplayer|psp-internetradioplayer|videos)\/((\d+)[\w\.-]+)/i',
                                                                                    // Audacious/AudiMusicStream/Amarok/BASS/OpenCORE/Dalvik/GnomeMplayer/MoC
                                                                                    // NSPlayer/PSP-InternetRadioPlayer/Videos
                '/(clementine|music player daemon)\s((\d+)[\w\.-]+)/i',               // Clementine/MPD
                '/(lg player|nexplayer)\s((\d+)[\d\.]+)/i',
                '/player\/(nexplayer|lg player)\s((\d+)[\w\.-]+)/i'                   // NexPlayer/LG Player
                ], [self::NAME, self::VERSION], [
                '/(nexplayer)\s((\d+)[\w\.-]+)/i'                                     // Nexplayer
                ], [self::NAME, self::VERSION], [
                '/(flrp)\/((\d+)[\w\.-]+)/i'                                          // Flip Player
                ], [[self::NAME, 'Flip Player'], self::VERSION], [
                '/(fstream|nativehost|queryseekspider|ia-archiver|facebookexternalhit)/i'
                                                                                    '// FStream/NativeHost/QuerySeekSpider/I'A Archiver/facebookexternalhit
                ], [self::NAME], [
                '/(gstreamer) souphttpsrc (?:\([^\)]+\)){0,1} libsoup\/((\d+)[\w\.-]+)/i'
                                                                                    // Gstreamer
                ], [self::NAME, self::VERSION], [
                '/(htc streaming player)\s[\w_]+\s\/\s((\d+)[\d\.]+)/i',              // HTC Streaming Player
                '/(java|python-urllib|python-requests|wget|libcurl)\/((\d+)[\w\.-_]+)/i',
                                                                                    // Java/urllib/requests/wget/cURL
                '/(lavf)((\d+)[\d\.]+)/i'                                             // Lavf (FFMPEG)
                ], [self::NAME, self::VERSION], [
                '/(htc_one_s)\/((\d+)[\d\.]+)/i'                                      // HTC One S
                ], [[self::NAME, /_/, ' '], self::VERSION], [
                '/(mplayer)(?:\s|\/)(?:(?:sherpya-){0,1}svn)(?:-|\s)(r\d+(?:-\d+[\w\.-]+){0,1})/i'
                                                                                    // MPlayer SVN
                ], [self::NAME, self::VERSION], [
                '/(mplayer)(?:\s|\/|[unkow-]+)((\d+)[\w\.-]+)/i'                      // MPlayer
                ], [self::NAME, self::VERSION], [
                '/(mplayer)/i',                                                       // MPlayer (no other info)
                '/(yourmuze)/i',                                                      // YourMuze
                '/(media player classic|nero showtime)/i'                             // Media Player Classic/Nero ShowTime
                ], [self::NAME], [
                '/(nero (?:home|scout))\/((\d+)[\w\.-]+)/i'                           // Nero Home/Nero Scout
                ], [self::NAME, self::VERSION], [
                '/(nokia\d+)\/((\d+)[\w\.-]+)/i'                                      // Nokia
                ], [self::NAME, self::VERSION], [
                '/\s(songbird)\/((\d+)[\w\.-]+)/i'                                    // Songbird/Philips-Songbird
                ], [self::NAME, self::VERSION], [
                '/(winamp)3 version ((\d+)[\w\.-]+)/i',                               // Winamp
                '/(winamp)\s((\d+)[\w\.-]+)/i',
                '/(winamp)mpeg\/((\d+)[\w\.-]+)/i'
                ], [self::NAME, self::VERSION], [
                '/(ocms-bot|tapinradio|tunein radio|unknown|winamp|inlight radio)/i'  // OCMS-bot/tap in radio/tunein/unknown/winamp (no other info)
                                                                                    // inlight radio
                ], [self::NAME], [
                '/(quicktime|rma|radioapp|radioclientapplication|soundtap|totem|stagefright|streamium)\/((\d+)[\w\.-]+)/i'
                                                                                    // QuickTime/RealMedia/RadioApp/RadioClientApplication/
                                                                                    // SoundTap/Totem/Stagefright/Streamium
                ], [self::NAME, self::VERSION], [
                '/(smp)((\d+)[\d\.]+)/i'                                              // SMP
                ], [self::NAME, self::VERSION], [
                '/(vlc) media player - version ((\d+)[\w\.]+)/i',                     // VLC Videolan
                '/(vlc)\/((\d+)[\w\.-]+)/i',
                '/(xbmc|gvfs|xine|xmms|irapp)\/((\d+)[\w\.-]+)/i,                    // XBMC/gvfs/Xine/XMMS/i'rapp
                '/(foobar2000)\/((\d+)[\d\.]+)/i',                                    // Foobar2000
                '/(itunes)\/((\d+)[\d\.]+)/i'                                         // iTunes
                ], [self::NAME, self::VERSION], [
                '/(wmplayer)\/((\d+)[\w\.-]+)/i',                                     // Windows Media Player
                '/(windows-media-player)\/((\d+)[\w\.-]+)/i'
                ], [[self::NAME, /-/, ' '], self::VERSION], [
                '/windows\/((\d+)[\w\.-]+) upnp\/[\d\.]+ dlnadoc\/[\d\.]+ (home media server)/i'
                                                                                    // Windows Media Server
                ], [self::VERSION, [self::NAME, 'Windows']], [
                '/(com\.riseupradioalarm)\/((\d+)[\d\.]*)/i'                          // RiseUP Radio Alarm
                ], [self::NAME, self::VERSION], [
                '/(rad.io)\s((\d+)[\d\.]+)/i',                                        // Rad.io
                '/(radio.(?:de|at|fr))\s((\d+)[\d\.]+)/i'
                ], [[self::NAME, 'rad.io'], self::VERSION]
                //////////////////////
                // Media players END
                ////////////////////*/

            ],

            'cpu' => [
                [

                '/(?:(amd|x(?:(?:86|64)[_-])?|wow|win)64)[;\)]/i'                     // AMD64
            ], [[self::ARCHITECTURE, 'amd64']], [

                '/(ia32(?=;))/i'                                                      // IA32 (quicktime)
            ], [[self::ARCHITECTURE, function ($str) {
                return $this->util->lowerize($str);
            }]], [

                '/((?:i[346]|x)86)[;\)]/i'                                            // IA32
            ], [[self::ARCHITECTURE, 'ia32']], [

                // PocketPC mistakenly identified as PowerPC
                '/windows\s(ce|mobile);\sppc;/i'
            ], [[self::ARCHITECTURE, 'arm']], [

                '/((?:ppc|powerpc)(?:64)?)(?:\smac|;|\))/i'                           // PowerPC
            ], [[self::ARCHITECTURE, '/ower/', '', function ($str) {
                return $this->util->lowerize($str);
            }]], [

                '/(sun4\w)[;\)]/i'                                                    // SPARC
            ], [[self::ARCHITECTURE, 'sparc']], [

                '/((?:avr32|ia64(?=;))|68k(?=\))|arm(?:64|(?=v\d+;))|(?=atmel\s)avr|(?:irix|mips|sparc)(?:64)?(?=;)|pa-risc)/i'
                // IA64, 68K, ARM/64, AVR/32, IRIX/64, MIPS/64, SPARC/64, PA-RISC
            ], [[self::ARCHITECTURE, function ($str) {
                return $this->util->lowerize($str);
            }]]
            ],

            'device' => [
                [

                '/\((ipad|playbook);[\w\s\);-]+(rim|apple)/i'                         // iPad/PlayBook
            ], [self::MODEL, self::VENDOR, [self::TYPE, self::TABLET]], [

                '/applecoremedia\/[\w\.]+ \((ipad)/'                                  // iPad
            ], [self::MODEL, [self::VENDOR, 'Apple'], [self::TYPE, self::TABLET]], [

                '/(apple\s{0,1}tv)/i'                                                 // Apple TV
            ], [[self::MODEL, 'Apple TV'], [self::VENDOR, 'Apple']], [

                '/(archos)\s(gamepad2?)/i',                                           // Archos
                '/(hp).+(touchpad)/i',                                                // HP TouchPad
                '/(hp).+(tablet)/i',                                                  // HP Tablet
                '/(kindle)\/([\w\.]+)/i',                                             // Kindle
                '/\s(nook)[\w\s]+build\/(\w+)/i',                                     // Nook
                '/(dell)\s(strea[kpr\s\d]*[\dko])/i'                                  // Dell Streak
            ], [self::VENDOR, self::MODEL, [self::TYPE, self::TABLET]], [

                '/(kf[A-z]+)\sbuild\/[\w\.]+.*silk\//i'                               // Kindle Fire HD
            ], [self::MODEL, [self::VENDOR, 'Amazon'], [self::TYPE, self::TABLET]], [
                '/(sd|kf)[0349hijorstuw]+\sbuild\/[\w\.]+.*silk\//i'                  // Fire Phone
            ], [[self::MODEL, function ($str, $map) {
                return $this->mapper->str($str, $map);
            }, $this->maps['device']['amazon']['model']], [self::VENDOR, 'Amazon'], [self::TYPE, self::MOBILE]], [

                '/\((ip[honed|\s\w*]+);.+(apple)/i'                                   // iPod/iPhone
            ], [self::MODEL, self::VENDOR, [self::TYPE, self::MOBILE]], [
                '/\((ip[honed|\s\w*]+);/i'                                            // iPod/iPhone
            ], [self::MODEL, [self::VENDOR, 'Apple'], [self::TYPE, self::MOBILE]], [

                '/(blackberry)[\s-]?(\w+)/i',                                         // BlackBerry
                '/(blackberry|benq|palm(?=\-)|sonyericsson|acer|asus|dell|meizu|motorola|polytron)[\s_-]?([\w-]+)*/i',
                // BenQ/Palm/Sony-Ericsson/Acer/Asus/Dell/Meizu/Motorola/Polytron
                '/(hp)\s([\w\s]+\w)/i',                                               // HP iPAQ
                '/(asus)-?(\w+)/i'                                                    // Asus
            ], [self::VENDOR, self::MODEL, [self::TYPE, self::MOBILE]], [
                '/\(bb10;\s(\w+)/i'                                                   // BlackBerry 10
            ], [self::MODEL, [self::VENDOR, 'BlackBerry'], [self::TYPE, self::MOBILE]], [
                // Asus Tablets
                '/android.+(transfo[prime\s]{4,10}\s\w+|eeepc|slider\s\w+|nexus 7|padfone)/i'
            ], [self::MODEL, [self::VENDOR, 'Asus'], [self::TYPE, self::TABLET]], [

                '/(sony)\s(tablet\s[ps])\sbuild\//i',                                  // Sony
                '/(sony)?(?:sgp.+)\sbuild\//i'
            ], [[self::VENDOR, 'Sony'], [self::MODEL, 'Xperia Tablet'], [self::TYPE, self::TABLET]], [
                '/android.+\s([c-g]\d{4}|so[-l]\w+)\sbuild\//i'
            ], [self::MODEL, [self::VENDOR, 'Sony'], [self::TYPE, self::MOBILE]], [

                '/\s(ouya)\s/i',                                                      // Ouya
                '/(nintendo)\s([wids3u]+)/i'                                          // Nintendo
            ], [self::VENDOR, self::MODEL, [self::TYPE, self::CONSOLE]], [

                '/android.+;\s(shield)\sbuild/i'                                      // Nvidia
            ], [self::MODEL, [self::VENDOR, 'Nvidia'], [self::TYPE, self::CONSOLE]], [

                '/(playstation\s[34portablevi]+)/i'                                   // Playstation
            ], [self::MODEL, [self::VENDOR, 'Sony'], [self::TYPE, self::CONSOLE]], [

                '/(sprint\s(\w+))/i'                                                  // Sprint Phones
            ], [[self::VENDOR, function ($str, $map) {
                return $this->mapper->str($str, $map);
            }, $this->maps['device']['sprint']['vendor']], [self::MODEL, function ($str, $map) {
                return $this->mapper->str($str, $map);
            }, $this->maps['device']['sprint']['model']], [self::TYPE, self::MOBILE]], [

                '/(lenovo)\s?(S(?:5000|6000)+(?:[-][\w+]))/i'                         // Lenovo tablets
            ], [self::VENDOR, self::MODEL, [self::TYPE, self::TABLET]], [

                '/(htc)[;_\s-]+([\w\s]+(?=\))|\w+)*/i',                               // HTC
                '/(zte)-(\w+)*/i',                                                    // ZTE
                '/(alcatel|geeksphone|lenovo|nexian|panasonic|(?=;\s)sony)[_\s-]?([\w-]+)*/i'
                // Alcatel/GeeksPhone/Lenovo/Nexian/Panasonic/Sony
            ], [self::VENDOR, [self::MODEL, '/_/', ' '], [self::TYPE, self::MOBILE]], [

                '/(nexus\s9)/i'                                                       // HTC Nexus 9
            ], [self::MODEL, [self::VENDOR, 'HTC'], [self::TYPE, self::TABLET]], [

                '/d\/huawei([\w\s-]+)[;\)]/i',
                '/(nexus\s6p)/i'                                                      // Huawei
            ], [self::MODEL, [self::VENDOR, 'Huawei'], [self::TYPE, self::MOBILE]], [

                '/(microsoft);\s(lumia[\s\w]+)/i'                                     // Microsoft Lumia
            ], [self::VENDOR, self::MODEL, [self::TYPE, self::MOBILE]], [

                '/[\s\(;](xbox(?:\sone)?)[\s\);]/i'                                   // Microsoft Xbox
            ], [self::MODEL, [self::VENDOR, 'Microsoft'], [self::TYPE, self::CONSOLE]], [
                '/(kin\.[onetw]{3})/i'                                                // Microsoft Kin
            ], [[self::MODEL, '/\./', ' '], [self::VENDOR, 'Microsoft'], [self::TYPE, self::MOBILE]], [

                // Motorola
                '/\s(milestone|droid(?:[2-4x]|\s(?:bionic|x2|pro|razr))?(:?\s4g)?)[\w\s]+build\//i',
                '/mot[\s-]?(\w+)*/i',
                '/(XT\d{3,4}) build\//i',
                '/(nexus\s6)/i'
            ], [self::MODEL, [self::VENDOR, 'Motorola'], [self::TYPE, self::MOBILE]], [
                '/android.+\s(mz60\d|xoom[\s2]{0,2})\sbuild\//i'
            ], [self::MODEL, [self::VENDOR, 'Motorola'], [self::TYPE, self::TABLET]], [

                '/hbbtv\/\d+\.\d+\.\d+\s+\([\w\s]*;\s*(\w[^;]*);([^;]*)/i'            // HbbTV devices
            ], [[self::VENDOR, 'util.trim'], [self::MODEL, 'util.trim'], [self::TYPE, self::SMARTTV]], [

                '/hbbtv.+maple;(\d+)/i'
            ], [[self::MODEL, '/^/', 'SmartTV'], [self::VENDOR, 'Samsung'], [self::TYPE, self::SMARTTV]], [

                '/\(dtv[\);].+(aquos)/i'                                              // Sharp
            ], [self::MODEL, [self::VENDOR, 'Sharp'], [self::TYPE, self::SMARTTV]], [

                '/android.+((sch-i[89]0\d|shw-m380s|gt-p\d{4}|gt-n\d+|sgh-t8[56]9|nexus 10))/i',
                '/((SM-T\w+))/i'
            ], [[self::VENDOR, 'Samsung'], self::MODEL, [self::TYPE, self::TABLET]], [                  // Samsung
                '/smart-tv.+(samsung)/i'
            ], [self::VENDOR, [self::TYPE, self::SMARTTV], self::MODEL], [
                '/((s[cgp]h-\w+|gt-\w+|galaxy\snexus|sm-\w[\w\d]+))/i',
                '/(sam[sung]*)[\s-]*(\w+-?[\w-]*)*/i',
                '/sec-((sgh\w+))/i'
            ], [[self::VENDOR, 'Samsung'], self::MODEL, [self::TYPE, self::MOBILE]], [

                '/sie-(\w+)*/i'                                                       // Siemens
            ], [self::MODEL, [self::VENDOR, 'Siemens'], [self::TYPE, self::MOBILE]], [

                '/(maemo|nokia).*(n900|lumia\s\d+)/i',                                // Nokia
                '/(nokia)[\s_-]?([\w-]+)*/i'
            ], [[self::VENDOR, 'Nokia'], self::MODEL, [self::TYPE, self::MOBILE]], [

                '/android\s3\.[\s\w;-]{10}(a\d{3})/i'                                 // Acer
            ], [self::MODEL, [self::VENDOR, 'Acer'], [self::TYPE, self::TABLET]], [

                '/android.+([vl]k\-?\d{3})\s+build/i'                                 // LG Tablet
            ], [self::MODEL, [self::VENDOR, 'LG'], [self::TYPE, self::TABLET]], [
                '/android\s3\.[\s\w;-]{10}(lg?)-([06cv9]{3,4})/i'                     // LG Tablet
            ], [[self::VENDOR, 'LG'], self::MODEL, [self::TYPE, self::TABLET]], [
                '/(lg) netcast\.tv/i'                                                 // LG SmartTV
            ], [self::VENDOR, self::MODEL, [self::TYPE, self::SMARTTV]], [
                '/(nexus\s[45])/i',                                                   // LG
                '/lg[e;\s\/-]+(\w+)*/i',
                '/android.+lg(\-?[\d\w]+)\s+build/i'
            ], [self::MODEL, [self::VENDOR, 'LG'], [self::TYPE, self::MOBILE]], [

                '/android.+(ideatab[a-z0-9\-\s]+)/i'                                  // Lenovo
            ], [self::MODEL, [self::VENDOR, 'Lenovo'], [self::TYPE, self::TABLET]], [

                '/linux;.+((jolla));/i'                                               // Jolla
            ], [self::VENDOR, self::MODEL, [self::TYPE, self::MOBILE]], [

                '/((pebble))app\/[\d\.]+\s/i'                                         // Pebble
            ], [self::VENDOR, self::MODEL, [self::TYPE, self::WEARABLE]], [

                '/android.+;\s(oppo)\s?([\w\s]+)\sbuild/i'                            // OPPO
            ], [self::VENDOR, self::MODEL, [self::TYPE, self::MOBILE]], [

                '/crkey/i'                                                            // Google Chromecast
            ], [[self::MODEL, 'Chromecast'], [self::VENDOR, 'Google']], [

                '/android.+;\s(glass)\s\d/i'                                          // Google Glass
            ], [self::MODEL, [self::VENDOR, 'Google'], [self::TYPE, self::WEARABLE]], [

                '/android.+;\s(pixel c)\s/i'                                          // Google Pixel C
            ], [self::MODEL, [self::VENDOR, 'Google'], [self::TYPE, self::TABLET]], [

                '/android.+;\s(pixel xl|pixel)\s/i'                                   // Google Pixel
            ], [self::MODEL, [self::VENDOR, 'Google'], [self::TYPE, self::MOBILE]], [

                '/android.+(\w+)\s+build\/hm\1/i',                                    // Xiaomi Hongmi 'numeric' models
                '/android.+(hm[\s\-_]*note?[\s_]*(?:\d\w)?)\s+build/i',               // Xiaomi Hongmi
                '/android.+(mi[\s\-_]*(?:one|one[\s_]plus|note lte)?[\s_]*(?:\d\w)?)\s+build/i'    // Xiaomi Mi
            ], [[self::MODEL, '/_/', ' '], [self::VENDOR, 'Xiaomi'], [self::TYPE, self::MOBILE]], [

                '/android.+;\s(m[1-5]\snote)\sbuild/i'                                // Meizu Tablet
            ], [self::MODEL, [self::VENDOR, 'Meizu'], [self::TYPE, self::TABLET]], [

                '/android.+a000(1)\s+build/i'                                         // OnePlus
            ], [self::MODEL, [self::VENDOR, 'OnePlus'], [self::TYPE, self::MOBILE]], [

                '/android.+[;\/]\s*(RCT[\d\w]+)\s+build/i'                            // RCA Tablets
            ], [self::MODEL, [self::VENDOR, 'RCA'], [self::TYPE, self::TABLET]], [

                '/android.+[;\/]\s*(Venue[\d\s]*)\s+build/i'                          // Dell Venue Tablets
            ], [self::MODEL, [self::VENDOR, 'Dell'], [self::TYPE, self::TABLET]], [

                '/android.+[;\/]\s*(Q[T|M][\d\w]+)\s+build/i'                         // Verizon Tablet
            ], [self::MODEL, [self::VENDOR, 'Verizon'], [self::TYPE, self::TABLET]], [

                '/android.+[;\/]\s+(Barnes[&\s]+Noble\s+|BN[RT])(V?.*)\s+build/i'     // Barnes & Noble Tablet
            ], [[self::VENDOR, 'Barnes & Noble'], self::MODEL, [self::TYPE, self::TABLET]], [

                '/android.+[;\/]\s+(TM\d{3}.*\b)\s+build/i'                           // Barnes & Noble Tablet
            ], [self::MODEL, [self::VENDOR, 'NuVision'], [self::TYPE, self::TABLET]], [

                '/android.+[;\/]\s*(zte)?.+(k\d{2})\s+build/i'                        // ZTE K Series Tablet
            ], [[self::VENDOR, 'ZTE'], self::MODEL, [self::TYPE, self::TABLET]], [

                '/android.+[;\/]\s*(gen\d{3})\s+build.*49h/i'                         // Swiss GEN Mobile
            ], [self::MODEL, [self::VENDOR, 'Swiss'], [self::TYPE, self::MOBILE]], [

                '/android.+[;\/]\s*(zur\d{3})\s+build/i'                              // Swiss ZUR Tablet
            ], [self::MODEL, [self::VENDOR, 'Swiss'], [self::TYPE, self::TABLET]], [

                '/android.+[;\/]\s*((Zeki)?TB.*\b)\s+build/i'                         // Zeki Tablets
            ], [self::MODEL, [self::VENDOR, 'Zeki'], [self::TYPE, self::TABLET]], [

                '/(android).+[;\/]\s+([YR]\d{2}x?.*)\s+build/i',
                '/android.+[;\/]\s+(Dragon[\-\s]+Touch\s+|DT)(.+)\s+build/i'          // Dragon Touch Tablet
            ], [[self::VENDOR, 'Dragon Touch'], self::MODEL, [self::TYPE, self::TABLET]], [

                '/android.+[;\/]\s*(NS-?.+)\s+build/i'                                // Insignia Tablets
            ], [self::MODEL, [self::VENDOR, 'Insignia'], [self::TYPE, self::TABLET]], [

                '/android.+[;\/]\s*((NX|Next)-?.+)\s+build/i'                         // NextBook Tablets
            ], [self::MODEL, [self::VENDOR, 'NextBook'], [self::TYPE, self::TABLET]], [

                '/android.+[;\/]\s*(Xtreme\_?)?(V(1[045]|2[015]|30|40|60|7[05]|90))\s+build/i'
            ], [[self::VENDOR, 'Voice'], self::MODEL, [self::TYPE, self::MOBILE]], [                    // Voice Xtreme Phones

                '/android.+[;\/]\s*(LVTEL\-?)?(V1[12])\s+build/i'                     // LvTel Phones
            ], [[self::VENDOR, 'LvTel'], self::MODEL, [self::TYPE, self::MOBILE]], [

                '/android.+[;\/]\s*(V(100MD|700NA|7011|917G).*\b)\s+build/i'          // Envizen Tablets
            ], [self::MODEL, [self::VENDOR, 'Envizen'], [self::TYPE, self::TABLET]], [

                '/android.+[;\/]\s*(Le[\s\-]+Pan)[\s\-]+(.*\b)\s+build/i'             // Le Pan Tablets
            ], [self::VENDOR, self::MODEL, [self::TYPE, self::TABLET]], [

                '/android.+[;\/]\s*(Trio[\s\-]*.*)\s+build/i'                         // MachSpeed Tablets
            ], [self::MODEL, [self::VENDOR, 'MachSpeed'], [self::TYPE, self::TABLET]], [

                '/android.+[;\/]\s*(Trinity)[\-\s]*(T\d{3})\s+build/i'                // Trinity Tablets
            ], [self::VENDOR, self::MODEL, [self::TYPE, self::TABLET]], [

                '/android.+[;\/]\s*TU_(1491)\s+build/i'                               // Rotor Tablets
            ], [self::MODEL, [self::VENDOR, 'Rotor'], [self::TYPE, self::TABLET]], [

                '/android.+(KS(.+))\s+build/i'                                        // Amazon Kindle Tablets
            ], [self::MODEL, [self::VENDOR, 'Amazon'], [self::TYPE, self::TABLET]], [

                '/android.+(Gigaset)[\s\-]+(Q.+)\s+build/i'                           // Gigaset Tablets
            ], [self::VENDOR, self::MODEL, [self::TYPE, self::TABLET]], [

                '/\s(tablet|tab)[;\/]/i',                                             // Unidentifiable Tablet
                '/\s(mobile)(?:[;\/]|\ssafari)/i'                                     // Unidentifiable Mobile
            ], [[self::TYPE, function ($str) {
                return $this->util->lowerize($str);
            }], self::VENDOR, self::MODEL], [

                '/(android.+)[;\/].+build/i'                                          // Generic Android Device
            ], [self::MODEL, [self::VENDOR, 'Generic']]


                /*//////////////////////////
                    // TODO: move to string map
                    ////////////////////////////
                    '/(C6603)/i'                                                          // Sony Xperia Z C6603
                    ], [[self::MODEL, 'Xperia Z C6603'], [self::VENDOR, 'Sony'], [self::TYPE, self::MOBILE]], [
                    '/(C6903)/i'                                                          // Sony Xperia Z 1
                    ], [[self::MODEL, 'Xperia Z 1'], [self::VENDOR, 'Sony'], [self::TYPE, self::MOBILE]], [
                    '/(SM-G900[F|H])/i'                                                   // Samsung Galaxy S5
                    ], [[self::MODEL, 'Galaxy S5'], [self::VENDOR, 'Samsung'], [self::TYPE, self::MOBILE]], [
                    '/(SM-G7102)/i'                                                       // Samsung Galaxy Grand 2
                    ], [[self::MODEL, 'Galaxy Grand 2'], [self::VENDOR, 'Samsung'], [self::TYPE, self::MOBILE]], [
                    '/(SM-G530H)/i'                                                       // Samsung Galaxy Grand Prime
                    ], [[self::MODEL, 'Galaxy Grand Prime'], [self::VENDOR, 'Samsung'], [self::TYPE, self::MOBILE]], [
                    '/(SM-G313HZ)/i'                                                      // Samsung Galaxy V
                    ], [[self::MODEL, 'Galaxy V'], [self::VENDOR, 'Samsung'], [self::TYPE, self::MOBILE]], [
                    '/(SM-T805)/i'                                                        // Samsung Galaxy Tab S 10.5
                    ], [[self::MODEL, 'Galaxy Tab S 10.5'], [self::VENDOR, 'Samsung'], [self::TYPE, self::TABLET]], [
                    '/(SM-G800F)/i'                                                       // Samsung Galaxy S5 Mini
                    ], [[self::MODEL, 'Galaxy S5 Mini'], [self::VENDOR, 'Samsung'], [self::TYPE, self::MOBILE]], [
                    '/(SM-T311)/i'                                                        // Samsung Galaxy Tab 3 8.0
                    ], [[self::MODEL, 'Galaxy Tab 3 8.0'], [self::VENDOR, 'Samsung'], [self::TYPE, self::TABLET]], [
                    '/(T3C)/i'                                                            // Advan Vandroid T3C
                    ], [self::MODEL, [self::VENDOR, 'Advan'], [self::TYPE, self::TABLET]], [
                    '/(ADVAN T1J\+)/i'                                                    // Advan Vandroid T1J+
                    ], [[self::MODEL, 'Vandroid T1J+'], [self::VENDOR, 'Advan'], [self::TYPE, self::TABLET]], [
                    '/(ADVAN S4A)/i'                                                      // Advan Vandroid S4A
                    ], [[self::MODEL, 'Vandroid S4A'], [self::VENDOR, 'Advan'], [self::TYPE, self::MOBILE]], [
                    '/(V972M)/i'                                                          // ZTE V972M
                    ], [self::MODEL, [self::VENDOR, 'ZTE'], [self::TYPE, self::MOBILE]], [
                    '/(i-mobile)\s(IQ\s[\d\.]+)/i'                                        // i-mobile IQ
                    ], [self::VENDOR, self::MODEL, [self::TYPE, self::MOBILE]], [
                    '/(IQ6.3)/i'                                                          // i-mobile IQ IQ 6.3
                    ], [[self::MODEL, 'IQ 6.3'], [self::VENDOR, 'i-mobile'], [self::TYPE, self::MOBILE]], [
                    '/(i-mobile)\s(i-style\s[\d\.]+)/i'                                   // i-mobile i-STYLE
                    ], [self::VENDOR, self::MODEL, [self::TYPE, self::MOBILE]], [
                    '/(i-STYLE2.1)/i'                                                     // i-mobile i-STYLE 2.1
                    ], [[self::MODEL, 'i-STYLE 2.1'], [self::VENDOR, 'i-mobile'], [self::TYPE, self::MOBILE]], [
                    '/(mobiistar touch LAI 512)/i'                                        // mobiistar touch LAI 512
                    ], [[self::MODEL, 'Touch LAI 512'], [self::VENDOR, 'mobiistar'], [self::TYPE, self::MOBILE]], [
                    /////////////
                    // END TODO
                    ///////////*/

            ],

            'engine' => [[

                '/windows.+\sedge\/([\w\.]+)/i'                                       // EdgeHTML
            ], [self::VERSION, [self::NAME, 'EdgeHTML']], [

                '/(presto)\/([\w\.]+)/i',                                             // Presto
                '/(webkit|trident|netfront|netsurf|amaya|lynx|w3m)\/([\w\.]+)/i',     // WebKit/Trident/NetFront/NetSurf/Amaya/Lynx/w3m
                '/(khtml|tasman|links)[\/\s]\(?([\w\.]+)/i',                          // KHTML/Tasman/Links
                '/(icab)[\/\s]([23]\.[\d\.]+)/i'                                      // iCab
            ], [self::NAME, self::VERSION], [

                '/rv\:([\w\.]+).*(gecko)/i'                                           // Gecko
            ], [self::VERSION, self::NAME]
            ],

            'os' => [[

                // Windows based
                '/microsoft\s(windows)\s(vista|xp)/i'                                 // Windows (iTunes)
            ], [self::NAME, self::VERSION], [
                '/(windows)\snt\s6\.2;\s(arm)/i',                                     // Windows RT
                '/(windows\sphone(?:\sos)*)[\s\/]?([\d\.\s]+\w)*/i',                  // Windows Phone
                '/(windows\smobile|windows)[\s\/]?([ntce\d\.\s]+\w)/i'
            ], [self::NAME, [self::VERSION, function ($str, $map) {
                return $this->mapper->str($str, $map);
            }, $this->maps['os']['windows']['version']]], [
                '/(win(?=3|9|n)|win\s9x\s)([nt\d\.]+)/i'
            ], [[self::NAME, 'Windows'], [self::VERSION, function ($str, $map) {
                return $this->mapper->str($str, $map);
            }, $this->maps['os']['windows']['version']]], [

                // Mobile/Embedded OS
                '/\((bb)(10);/i'                                                      // BlackBerry 10
            ], [[self::NAME, 'BlackBerry'], self::VERSION], [
                '/(blackberry)\w*\/?([\w\.]+)*/i',                                    // Blackberry
                '/(tizen)[\/\s]([\w\.]+)/i',                                          // Tizen
                '/(android|webos|palm\sos|qnx|bada|rim\stablet\sos|meego|contiki)[\/\s-]?([\w\.]+)*/i',
                // Android/WebOS/Palm/QNX/Bada/RIM/MeeGo/Contiki
                '/linux;.+(sailfish);/i'                                              // Sailfish OS
            ], [self::NAME, self::VERSION], [
                '/(symbian\s?os|symbos|s60(?=;))[\/\s-]?([\w\.]+)*/i'                 // Symbian
            ], [[self::NAME, 'Symbian'], self::VERSION], [
                '/\((series40);/i'                                                    // Series 40
            ], [self::NAME], [
                '/mozilla.+\(mobile;.+gecko.+firefox/i'                               // Firefox OS
            ], [[self::NAME, 'Firefox OS'], self::VERSION], [

                // Console
                '/(nintendo|playstation)\s([wids34portablevu]+)/i',                   // Nintendo/Playstation

                // GNU/Linux based
                '/(mint)[\/\s\(]?(\w+)*/i',                                           // Mint
                '/(mageia|vectorlinux)[;\s]/i',                                       // Mageia/VectorLinux
                '/(joli|[kxln]?ubuntu|debian|[open]*suse|gentoo|(?=\s)arch|slackware|fedora|mandriva|centos|pclinuxos|redhat|zenwalk|linpus)[\/\s-]?(?!chrom)([\w\.-]+)*/i',
                // Joli/Ubuntu/Debian/SUSE/Gentoo/Arch/Slackware
                // Fedora/Mandriva/CentOS/PCLinuxOS/RedHat/Zenwalk/Linpus
                '/(hurd|linux)\s?([\w\.]+)*/i',                                       // Hurd/Linux
                '/(gnu)\s?([\w\.]+)*/i'                                               // GNU
            ], [self::NAME, self::VERSION], [

                '/(cros)\s[\w]+\s([\w\.]+\w)/i'                                       // Chromium OS
            ], [[self::NAME, 'Chromium OS'], self::VERSION], [

                // Solaris
                '/(sunos)\s?([\w\.]+\d)*/i'                                           // Solaris
            ], [[self::NAME, 'Solaris'], self::VERSION], [

                // BSD based
                '/\s([frentopc-]{0,4}bsd|dragonfly)\s?([\w\.]+)*/i'                   // FreeBSD/NetBSD/OpenBSD/PC-BSD/DragonFly
            ], [self::NAME, self::VERSION], [

                '/(haiku)\s(\w+)/i'                                                  // Haiku
            ], [self::NAME, self::VERSION], [

                '/cfnetwork\/.+darwin/i',
                '/ip[honead]+(?:.*os\s([\w]+)*\slike\smac|;\sopera)/i'                // iOS
            ], [[self::VERSION, '/_/', '.'], [self::NAME, 'iOS']], [

                '/(mac\sos\sx)\s?([\w\s\.]+\w)*/i',
                '/(macintosh|mac(?=_powerpc)\s)/i'                                    // Mac OS
            ], [[self::NAME, 'Mac OS'], [self::VERSION, '/_/', '.']], [

                // Other
                '/((?:open)?solaris)[\/\s-]?([\w\.]+)*/i',                            // Solaris
                '/(aix)\s((\d)(?=\.|\)|\s)[\w\.]*)*/i',                               // AIX
                '/(plan\s9|minix|beos|os\/2|amigaos|morphos|risc\sos|openvms)/i',
                // Plan9/Minix/BeOS/OS2/AmigaOS/MorphOS/RISCOS/OpenVMS
                '/(unix)\s?([\w\.]+)*/i'                                              // UNIX
            ], [self::NAME, self::VERSION]
            ]
        ];


        if (is_object($uastring)) {
            $extensions = $uastring;
            $uastring = null;
        }

        if (is_null($uastring) && isset($_SERVER['HTTP_USER_AGENT'])) {
            $this->ua = $_SERVER['HTTP_USER_AGENT'];
        } else {
            $this->ua = $uastring;
        }

        if ($extensions) $this->rgxmap = $this->util->extend($this->rgxmap, $extensions);

    }


    public function getBrowser()
    {
        $browser = ['name' => null, 'version' => null];
        $this->mapper->rgx($browser, $this->ua, $this->rgxmap['browser']);
        $browser['major'] = $this->util->major($browser['version']);
        return $browser;
    }

    public function getCPU()
    {
        $cpu = ['architecture' => null];
        $this->mapper->rgx($cpu, $this->ua, $this->rgxmap['cpu']);
        return $cpu;
    }

    public function getDevice()
    {
        $device = ['vendor' => null, 'model' => null, 'type' => null];
        $this->mapper->rgx($device, $this->ua, $this->rgxmap['device']);
        return $device;
    }

    public function getEngine()
    {
        $engine = ['name' => null, 'version' => null];
        $this->mapper->rgx($engine, $this->ua, $this->rgxmap['engine']);
        return $engine;
    }

    public function getOS()
    {
        $os = ['name' => null, 'version' => null];
        $this->mapper->rgx($os, $this->ua, $this->rgxmap['os']);
        return $os;
    }

    public function getResult()
    {
        return [
            'ua' => $this->getUA(),
            'browser' => $this->getBrowser(),
            'engine' => $this->getEngine(),
            'os' => $this->getOS(),
            'device' => $this->getDevice(),
            'cpu' => $this->getCPU()
        ];
    }

    public function getUA()
    {
        return $this->ua;
    }

    public function setUA($ua)
    {
        $this->ua = $ua;
        return $this;
    }
}