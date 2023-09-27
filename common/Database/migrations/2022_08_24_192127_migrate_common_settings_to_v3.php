<?php

use Common\Admin\Appearance\Themes\CssTheme;
use Common\Settings\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Color\Factory;
use Spatie\Color\Hex;

return new class extends Migration {
    protected array $svgToPrefix = [
        'upload.svg',
        'web-devices.svg',
        'share.svg',
        'add-file.svg',
        'authentication.svg',
        'right-direction.svg',
    ];

    public function up()
    {
        $this->migrateLandingPage();
        $this->migrateMenus();
        $this->migrateThemes();
        $this->migrateAds();
        $this->migrateGenreImages();

        Setting::where('name', 'homepage.type')
            ->where('value', 'Channel')
            ->update(['value' => 'channel']);
    }

    protected function migrateGenreImages()
    {
        if (Schema::hasTable('genres')) {
            DB::table('genres')->update([
                'image' => DB::raw(
                    'REPLACE(image, "client/assets/images/", "images/")',
                ),
            ]);
        }
    }

    protected function migrateLandingPage()
    {
        $homepage = Setting::where('name', 'homepage.appearance')->first();
        if (!$homepage) {
            return;
        }

        $value = $homepage['value'];
        $value = str_replace('client\/assets\/images\/', 'images\/', $value);
        $value = str_replace('client/assets/images/', 'images/', $value);
        $value = str_replace('landing-bg.svg', 'landing-bg.jpg', $value);

        foreach ($this->svgToPrefix as $svg) {
            $value = str_replace($svg, "images/landing/$svg", $value);
        }

        if ( ! isset($home))

        Setting::where('name', 'homepage.appearance')->update([
            'value' => $value,
        ]);
    }

    protected function migrateMenus()
    {
        $menus = Setting::where('name', 'menus')->first();
        if (!$menus) {
            return;
        }

        $menus = json_decode($menus['value'], true);

        // convert menus "position" string to "positions" array
        foreach ($menus as $menuKey => $menu) {
            if (!isset($menu['positions'])) {
                $menus[$menuKey]['positions'] = [$menu['position']];
                unset($menus[$menuKey]['position']);
            }

            foreach ($menu['items'] as $itemKey => $item) {
                // remove workspaces menu item
                if (Arr::get($item, 'label') === 'Workspaces') {
                    unset($menus[$menuKey]['items'][$itemKey]);
                }

                // prefix menu items with slash, if not prefixed already
                if (
                    $item['type'] === 'route' &&
                    !str_starts_with($item['action'], '/')
                ) {
                    $menus[$menuKey]['items'][$itemKey]['action'] =
                        '/' . $item['action'];
                }

                if (!isset($item['id'])) {
                    $menus[$menuKey]['items'][$itemKey]['id'] = Str::random(6);
                }

                // migrate icons to svg path config from simple string
                if (!isset($item['icon'])) {
                    continue;
                }
                if ($item['icon'] === 'people') {
                    $menus[$menuKey]['items'][$itemKey]['icon'] = [
                        [
                            'tag' => 'path',
                            'attr' => [
                                'd' =>
                                    'M9 13.75c-2.34 0-7 1.17-7 3.5V19h14v-1.75c0-2.33-4.66-3.5-7-3.5zM4.34 17c.84-.58 2.87-1.25 4.66-1.25s3.82.67 4.66 1.25H4.34zM9 12c1.93 0 3.5-1.57 3.5-3.5S10.93 5 9 5 5.5 6.57 5.5 8.5 7.07 12 9 12zm0-5c.83 0 1.5.67 1.5 1.5S9.83 10 9 10s-1.5-.67-1.5-1.5S8.17 7 9 7zm7.04 6.81c1.16.84 1.96 1.96 1.96 3.44V19h4v-1.75c0-2.02-3.5-3.17-5.96-3.44zM15 12c1.93 0 3.5-1.57 3.5-3.5S16.93 5 15 5c-.54 0-1.04.13-1.5.35.63.89 1 1.98 1 3.15s-.37 2.26-1 3.15c.46.22.96.35 1.5.35z',
                            ],
                        ],
                    ];
                }
                if ($item['icon'] === 'access-time') {
                    $menus[$menuKey]['items'][$itemKey]['icon'] = [
                        [
                            'tag' => 'path',
                            'attr' => [
                                'd' =>
                                    'M13 3c-4.97 0-9 4.03-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42C8.27 19.99 10.51 21 13 21c4.97 0 9-4.03 9-9s-4.03-9-9-9zm-1 5v5l4.25 2.52.77-1.28-3.52-2.09V8z',
                            ],
                        ],
                    ];
                }
                if ($item['icon'] === 'star') {
                    $menus[$menuKey]['items'][$itemKey]['icon'] = [
                        [
                            'tag' => 'path',
                            'attr' => [
                                'd' =>
                                    'M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21 12 17.27z',
                            ],
                        ],
                    ];
                }
                if ($item['icon'] === 'delete') {
                    $menus[$menuKey]['items'][$itemKey]['icon'] = [
                        [
                            'tag' => 'path',
                            'attr' => [
                                'd' =>
                                    'M16 9v10H8V9h8m-1.5-6h-5l-1 1H5v2h14V4h-3.5l-1-1zM18 7H6v12c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7z',
                            ],
                        ],
                    ];
                }
                if ($item['icon'] === 'home') {
                    $menus[$menuKey]['items'][$itemKey]['icon'] = [
                        [
                            'tag' => 'path',
                            'attr' => [
                                'd' =>
                                    'm12 5.69 5 4.5V18h-2v-6H9v6H7v-7.81l5-4.5M12 3 2 12h3v8h6v-6h2v6h6v-8h3L12 3z',
                            ],
                        ],
                    ];
                }
                if ($item['icon'] === 'link') {
                    $menus[$menuKey]['items'][$itemKey]['icon'] = [
                        [
                            'tag' => 'path',
                            'attr' => [
                                'd' =>
                                    'M17 7h-4v2h4c1.65 0 3 1.35 3 3s-1.35 3-3 3h-4v2h4c2.76 0 5-2.24 5-5s-2.24-5-5-5zm-6 8H7c-1.65 0-3-1.35-3-3s1.35-3 3-3h4V7H7c-2.76 0-5 2.24-5 5s2.24 5 5 5h4v-2zm-3-4h8v2H8z',
                            ],
                        ],
                    ];
                }
                if ($item['icon'] === 'instagram') {
                    $menus[$menuKey]['items'][$itemKey]['icon'] = [
                        [
                            'tag' => 'path',
                            'attr' => [
                                'd' =>
                                    'M20 5h-3.2L15 3H9L7.2 5H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 14h-8v-1c-2.8 0-5-2.2-5-5s2.2-5 5-5V7h8v12zm-3-6c0-2.8-2.2-5-5-5v1.8c1.8 0 3.2 1.4 3.2 3.2s-1.4 3.2-3.2 3.2V18c2.8 0 5-2.2 5-5zm-8.2 0c0 1.8 1.4 3.2 3.2 3.2V9.8c-1.8 0-3.2 1.4-3.2 3.2z',
                            ],
                        ],
                    ];
                }
                if ($item['icon'] === 'dashboard') {
                    $menus[$menuKey]['items'][$itemKey]['icon'] = [
                        [
                            'tag' => 'path',
                            'attr' => [
                                'd' =>
                                    'M19 5v2h-4V5h4M9 5v6H5V5h4m10 8v6h-4v-6h4M9 17v2H5v-2h4M21 3h-8v6h8V3zM11 3H3v10h8V3zm10 8h-8v10h8V11zm-10 4H3v6h8v-6z',
                            ],
                        ],
                    ];
                }
                if ($item['icon'] === 'www') {
                    $menus[$menuKey]['items'][$itemKey]['icon'] = [
                        [
                            'tag' => 'path',
                            'attr' => [
                                'd' =>
                                    'M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zm6.93 6h-2.95c-.32-1.25-.78-2.45-1.38-3.56 1.84.63 3.37 1.91 4.33 3.56zM12 4.04c.83 1.2 1.48 2.53 1.91 3.96h-3.82c.43-1.43 1.08-2.76 1.91-3.96zM4.26 14C4.1 13.36 4 12.69 4 12s.1-1.36.26-2h3.38c-.08.66-.14 1.32-.14 2s.06 1.34.14 2H4.26zm.82 2h2.95c.32 1.25.78 2.45 1.38 3.56-1.84-.63-3.37-1.9-4.33-3.56zm2.95-8H5.08c.96-1.66 2.49-2.93 4.33-3.56C8.81 5.55 8.35 6.75 8.03 8zM12 19.96c-.83-1.2-1.48-2.53-1.91-3.96h3.82c-.43 1.43-1.08 2.76-1.91 3.96zM14.34 14H9.66c-.09-.66-.16-1.32-.16-2s.07-1.35.16-2h4.68c.09.65.16 1.32.16 2s-.07 1.34-.16 2zm.25 5.56c.6-1.11 1.06-2.31 1.38-3.56h2.95c-.96 1.65-2.49 2.93-4.33 3.56zM16.36 14c.08-.66.14-1.32.14-2s-.06-1.34-.14-2h3.38c.16.64.26 1.31.26 2s-.1 1.36-.26 2h-3.38z',
                            ],
                        ],
                    ];
                }
                if ($item['icon'] === 'tooltip') {
                    $menus[$menuKey]['items'][$itemKey]['icon'] = [
                        [
                            'tag' => 'path',
                            'attr' => [
                                'd' =>
                                    'M21.99 4c0-1.1-.89-2-1.99-2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h14l4 4-.01-18zM20 4v13.17L18.83 16H4V4h16zM6 12h12v2H6zm0-3h12v2H6zm0-3h12v2H6z',
                            ],
                        ],
                    ];
                }
                if ($item['icon'] === 'page') {
                    $menus[$menuKey]['items'][$itemKey]['icon'] = [
                        [
                            'tag' => 'path',
                            'attr' => [
                                'd' =>
                                    'm22 3-1.67 1.67L18.67 3 17 4.67 15.33 3l-1.66 1.67L12 3l-1.67 1.67L8.67 3 7 4.67 5.33 3 3.67 4.67 2 3v16c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V3zM11 19H4v-6h7v6zm9 0h-7v-2h7v2zm0-4h-7v-2h7v2zm0-4H4V8h16v3z',
                            ],
                        ],
                    ];
                }
                if ($item['icon'] === 'tracking') {
                    $menus[$menuKey]['items'][$itemKey]['icon'] = [
                        [
                            'tag' => 'path',
                            'attr' => [
                                'd' =>
                                    'M13.02 19.93v2.02c2.01-.2 3.84-1 5.32-2.21l-1.42-1.43c-1.11.86-2.44 1.44-3.9 1.62zM4.03 12c0-4.05 3.03-7.41 6.95-7.93V2.05C5.95 2.58 2.03 6.84 2.03 12c0 5.16 3.92 9.42 8.95 9.95v-2.02c-3.92-.52-6.95-3.88-6.95-7.93zm15.92-1h2.02c-.2-2.01-1-3.84-2.21-5.32l-1.43 1.43c.86 1.1 1.44 2.43 1.62 3.89zm-1.61-6.74c-1.48-1.21-3.32-2.01-5.32-2.21v2.02c1.46.18 2.79.76 3.9 1.62l1.42-1.43zm-.01 12.64 1.43 1.42c1.21-1.48 2.01-3.31 2.21-5.32h-2.02c-.18 1.46-.76 2.79-1.62 3.9z',
                            ],
                        ],
                        [
                            'tag' => 'path',
                            'attr' => [
                                'd' =>
                                    'M16 11.1C16 8.61 14.1 7 12 7s-4 1.61-4 4.1c0 1.66 1.33 3.63 4 5.9 2.67-2.27 4-4.24 4-5.9zm-4 .9c-.59 0-1.07-.48-1.07-1.07 0-.59.48-1.07 1.07-1.07s1.07.48 1.07 1.07c0 .59-.48 1.07-1.07 1.07z',
                            ],
                        ],
                    ];
                }
                if ($item['icon'] === 'facebook') {
                    $menus[$menuKey]['items'][$itemKey]['icon'] = [
                        [
                            'tag' => 'path',
                            'attr' => [
                                'd' =>
                                    'M19 3H5a2 2 0 00-2 2v14a2 2 0 002 2h7.621v-6.961h-2.343v-2.725h2.343V9.309c0-2.324 1.421-3.591 3.495-3.591.699-.002 1.397.034 2.092.105v2.43H16.78c-1.13 0-1.35.534-1.35 1.322v1.735h2.7l-.351 2.725h-2.365V21H19a2 2 0 002-2V5a2 2 0 00-2-2z',
                            ],
                        ],
                    ];
                }
                if ($item['icon'] === 'twitter') {
                    $menus[$menuKey]['items'][$itemKey]['icon'] = [
                        [
                            'tag' => 'path',
                            'attr' => [
                                'd' =>
                                    'M24 4.3c-.898.4-1.8.7-2.8.802 1-.602 1.8-1.602 2.198-2.704-1 .602-2 1-3.097 1.204C19.3 2.602 18 2 16.6 2a4.907 4.907 0 00-4.9 4.898c0 .403 0 .801.102 1.102C7.7 7.8 4.102 5.898 1.7 2.898c-.5.704-.7 1.602-.7 2.5 0 1.704.898 3.204 2.2 4.102-.802-.102-1.598-.3-2.2-.602V9c0 2.398 1.7 4.398 3.898 4.8-.398.098-.796.2-1.296.2-.301 0-.602 0-.903-.102.602 2 2.403 3.403 4.602 3.403-1.7 1.3-3.801 2.097-6.102 2.097-.398 0-.8 0-1.199-.097C2.2 20.699 4.8 21.5 7.5 21.5c9.102 0 14-7.5 14-14v-.602c1-.699 1.8-1.597 2.5-2.597',
                            ],
                        ],
                    ];
                }
                if ($item['icon'] === 'youtube') {
                    $menus[$menuKey]['items'][$itemKey]['icon'] = [
                        [
                            'tag' => 'path',
                            'attr' => [
                                'd' =>
                                    'M5.68 2l1.478 5.344v2.562H8.44V7.344L9.937 2h-1.29l-.538 2.432a27.21 27.21 0 00-.29 1.515h-.04c-.063-.42-.159-.93-.29-1.525L6.97 2H5.68zm5.752 2.018c-.434 0-.784.084-1.051.257-.267.172-.464.448-.59.825-.125.377-.187.876-.187 1.498v.84c0 .615.054 1.107.164 1.478.11.371.295.644.556.82.261.176.62.264 1.078.264.446 0 .8-.087 1.06-.26.26-.173.45-.444.565-.818.116-.374.174-.869.174-1.485v-.84c0-.62-.059-1.118-.178-1.492-.119-.373-.308-.648-.566-.824-.258-.176-.598-.263-1.025-.263zm2.447.113v4.314c0 .534.09.927.271 1.178.182.251.465.377.848.377.552 0 .968-.267 1.244-.8h.028l.113.706H17.4V4.131h-1.298v4.588a.635.635 0 01-.23.263.569.569 0 01-.325.104c-.132 0-.226-.054-.283-.164-.057-.11-.086-.295-.086-.553V4.131h-1.3zm-2.477.781c.182 0 .311.095.383.287.072.191.108.495.108.91v1.8c0 .426-.036.735-.108.923-.072.188-.2.282-.38.283-.183 0-.309-.095-.378-.283-.07-.188-.103-.497-.103-.924V6.11c0-.414.035-.718.107-.91.072-.19.195-.287.371-.287zM5 11c-1.1 0-2 .9-2 2v7c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2v-7c0-1.1-.9-2-2-2H5zm7.049 2h1.056v2.568h.008c.095-.186.232-.335.407-.449.175-.114.364-.17.566-.17.26 0 .463.07.611.207.148.138.257.361.323.668.066.308.097.735.097 1.281v.772h.002c0 .727-.089 1.26-.264 1.602-.175.342-.447.513-.818.513-.207 0-.394-.047-.564-.142a.93.93 0 01-.383-.391h-.024l-.11.46h-.907V13zm-6.563.246h3.252v.885h-1.09v5.789H6.576v-5.79h-1.09v-.884zm11.612 1.705c.376 0 .665.07.867.207.2.138.343.354.426.645.082.292.123.695.123 1.209v.836h-1.836v.248c0 .313.008.547.027.703.02.156.057.27.115.342.058.072.148.107.27.107.164 0 .277-.064.338-.191.06-.127.094-.338.1-.635l.947.055a1.6 1.6 0 01.007.175c0 .451-.124.788-.37 1.01-.248.223-.595.334-1.046.334-.54 0-.92-.17-1.138-.51-.218-.339-.326-.863-.326-1.574v-.851c0-.732.112-1.267.337-1.604.225-.337.613-.506 1.159-.506zm-8.688.094h1.1v3.58c0 .217.024.373.072.465.048.093.126.139.238.139a.486.486 0 00.276-.088.538.538 0 00.193-.223v-3.873h1.1v4.875h-.862l-.093-.598h-.026c-.234.452-.584.678-1.05.678-.325 0-.561-.106-.715-.318-.154-.212-.233-.544-.233-.994v-3.643zm8.664.648c-.117 0-.204.036-.26.104-.056.069-.093.182-.11.338a6.504 6.504 0 00-.028.71v.35h.803v-.35c0-.312-.01-.548-.032-.71-.02-.162-.059-.276-.115-.342-.056-.066-.14-.1-.258-.1zm-3.482.036a.418.418 0 00-.293.126.699.699 0 00-.192.327v2.767a.487.487 0 00.438.256.337.337 0 00.277-.127c.07-.085.12-.228.149-.43.029-.2.043-.48.043-.835v-.627c0-.382-.011-.676-.035-.883-.024-.207-.067-.357-.127-.444a.299.299 0 00-.26-.13z',
                            ],
                        ],
                    ];
                }
                if ($item['icon'] === 'album') {
                    $menus[$menuKey]['items'][$itemKey]['icon'] = [
                        [
                            'tag' => 'path',
                            'attr' => [
                                'd' =>
                                    'M12 16.5q1.875 0 3.188-1.312Q16.5 13.875 16.5 12q0-1.875-1.312-3.188Q13.875 7.5 12 7.5q-1.875 0-3.188 1.312Q7.5 10.125 7.5 12q0 1.875 1.312 3.188Q10.125 16.5 12 16.5Zm0-3.5q-.425 0-.712-.288Q11 12.425 11 12t.288-.713Q11.575 11 12 11t.713.287Q13 11.575 13 12t-.287.712Q12.425 13 12 13Zm0 9q-2.075 0-3.9-.788-1.825-.787-3.175-2.137-1.35-1.35-2.137-3.175Q2 14.075 2 12t.788-3.9q.787-1.825 2.137-3.175 1.35-1.35 3.175-2.138Q9.925 2 12 2t3.9.787q1.825.788 3.175 2.138 1.35 1.35 2.137 3.175Q22 9.925 22 12t-.788 3.9q-.787 1.825-2.137 3.175-1.35 1.35-3.175 2.137Q14.075 22 12 22Zm0-2q3.35 0 5.675-2.325Q20 15.35 20 12q0-3.35-2.325-5.675Q15.35 4 12 4 8.65 4 6.325 6.325 4 8.65 4 12q0 3.35 2.325 5.675Q8.65 20 12 20Zm0-8Z',
                            ],
                        ],
                    ];
                }
                if ($item['icon'] === 'local-offer') {
                    $menus[$menuKey]['items'][$itemKey]['icon'] = [
                        [
                            'tag' => 'path',
                            'attr' => [
                                'd' =>
                                    'M14.25 21.4q-.575.575-1.425.575-.85 0-1.425-.575l-8.8-8.8q-.275-.275-.437-.65Q2 11.575 2 11.15V4q0-.825.588-1.413Q3.175 2 4 2h7.15q.425 0 .8.162.375.163.65.438l8.8 8.825q.575.575.575 1.412 0 .838-.575 1.413ZM12.825 20l7.15-7.15L11.15 4H4v7.15ZM6.5 8q.625 0 1.062-.438Q8 7.125 8 6.5t-.438-1.062Q7.125 5 6.5 5t-1.062.438Q5 5.875 5 6.5t.438 1.062Q5.875 8 6.5 8ZM4 4Z',
                            ],
                        ],
                    ];
                }
                if ($item['icon'] === 'trending-up') {
                    $menus[$menuKey]['items'][$itemKey]['icon'] = [
                        [
                            'tag' => 'path',
                            'attr' => [
                                'd' =>
                                    'M3.4 18 2 16.6l7.4-7.45 4 4L18.6 8H16V6h6v6h-2V9.4L13.4 16l-4-4Z',
                            ],
                        ],
                    ];
                }
                if ($item['icon'] === 'new-releases') {
                    $menus[$menuKey]['items'][$itemKey]['icon'] = [
                        [
                            'tag' => 'path',
                            'attr' => [
                                'd' =>
                                    'm8.6 22.5-1.9-3.2-3.6-.8.35-3.7L1 12l2.45-2.8-.35-3.7 3.6-.8 1.9-3.2L12 2.95l3.4-1.45 1.9 3.2 3.6.8-.35 3.7L23 12l-2.45 2.8.35 3.7-3.6.8-1.9 3.2-3.4-1.45Zm.85-2.55 2.55-1.1 2.6 1.1 1.4-2.4 2.75-.65-.25-2.8 1.85-2.1-1.85-2.15.25-2.8-2.75-.6-1.45-2.4L12 5.15l-2.6-1.1L8 6.45l-2.75.6.25 2.8L3.65 12l1.85 2.1-.25 2.85 2.75.6ZM12 12Zm-1.05 3.55L16.6 9.9l-1.4-1.45-4.25 4.25-2.15-2.1L7.4 12Z',
                            ],
                        ],
                    ];
                }
                if ($item['icon'] === 'audiotrack') {
                    $menus[$menuKey]['items'][$itemKey]['icon'] = [
                        [
                            'tag' => 'path',
                            'attr' => [
                                'd' =>
                                    'M10 21q-1.65 0-2.825-1.175Q6 18.65 6 17q0-1.65 1.175-2.825Q8.35 13 10 13q.575 0 1.062.137.488.138.938.413V3h6v4h-4v10q0 1.65-1.175 2.825Q11.65 21 10 21Z',
                            ],
                        ],
                    ];
                }
                if ($item['icon'] === 'mic') {
                    $menus[$menuKey]['items'][$itemKey]['icon'] = [
                        [
                            'tag' => 'path',
                            'attr' => [
                                'd' =>
                                    'M12 14q-1.25 0-2.125-.875T9 11V5q0-1.25.875-2.125T12 2q1.25 0 2.125.875T15 5v6q0 1.25-.875 2.125T12 14Zm0-6Zm-1 13v-3.075q-2.6-.35-4.3-2.325Q5 13.625 5 11h2q0 2.075 1.463 3.537Q9.925 16 12 16t3.538-1.463Q17 13.075 17 11h2q0 2.625-1.7 4.6-1.7 1.975-4.3 2.325V21Zm1-9q.425 0 .713-.288Q13 11.425 13 11V5q0-.425-.287-.713Q12.425 4 12 4t-.712.287Q11 4.575 11 5v6q0 .425.288.712.287.288.712.288Z',
                            ],
                        ],
                    ];
                }
                if ($item['icon'] === 'history') {
                    $menus[$menuKey]['items'][$itemKey]['icon'] = [
                        [
                            'tag' => 'path',
                            'attr' => [
                                'd' =>
                                    'M12 21q-3.45 0-6.012-2.288Q3.425 16.425 3.05 13H5.1q.35 2.6 2.312 4.3Q9.375 19 12 19q2.925 0 4.962-2.038Q19 14.925 19 12t-2.038-4.963Q14.925 5 12 5q-1.725 0-3.225.8T6.25 8H9v2H3V4h2v2.35q1.275-1.6 3.113-2.475Q9.95 3 12 3q1.875 0 3.513.712 1.637.713 2.85 1.925 1.212 1.213 1.925 2.85Q21 10.125 21 12t-.712 3.512q-.713 1.638-1.925 2.85-1.213 1.213-2.85 1.926Q13.875 21 12 21Zm2.8-4.8L11 12.4V7h2v4.6l3.2 3.2Z',
                            ],
                        ],
                    ];
                }
                if ($item['icon'] === 'search') {
                    $menus[$menuKey]['items'][$itemKey]['icon'] = [
                        [
                            'tag' => 'path',
                            'attr' => [
                                'd' =>
                                    'm19.6 21-6.3-6.3q-.75.6-1.725.95Q10.6 16 9.5 16q-2.725 0-4.612-1.887Q3 12.225 3 9.5q0-2.725 1.888-4.613Q6.775 3 9.5 3t4.613 1.887Q16 6.775 16 9.5q0 1.1-.35 2.075-.35.975-.95 1.725l6.3 6.3ZM9.5 14q1.875 0 3.188-1.312Q14 11.375 14 9.5q0-1.875-1.312-3.188Q11.375 5 9.5 5 7.625 5 6.312 6.312 5 7.625 5 9.5q0 1.875 1.312 3.188Q7.625 14 9.5 14Z',
                            ],
                        ],
                    ];
                }
                if ($item['icon'] === 'library-music') {
                    $menus[$menuKey]['items'][$itemKey]['icon'] = [
                        [
                            'tag' => 'path',
                            'attr' => [
                                'd' =>
                                    'M12.5 15q1.05 0 1.775-.725Q15 13.55 15 12.5V7h3V5h-4v5.5q-.325-.25-.7-.375-.375-.125-.8-.125-1.05 0-1.775.725Q10 11.45 10 12.5q0 1.05.725 1.775Q11.45 15 12.5 15ZM8 18q-.825 0-1.412-.587Q6 16.825 6 16V4q0-.825.588-1.413Q7.175 2 8 2h12q.825 0 1.413.587Q22 3.175 22 4v12q0 .825-.587 1.413Q20.825 18 20 18Zm0-2h12V4H8v12Zm-4 6q-.825 0-1.412-.587Q2 20.825 2 20V6h2v14h14v2ZM8 4v12V4Z',
                            ],
                        ],
                    ];
                }
            }
        }

        Setting::where('name', 'menus')->update([
            'value' => json_encode($menus),
        ]);
    }

    protected function migrateThemes()
    {
        CssTheme::all()->each(function (CssTheme $theme) {
            $newColors = [];
            $oldColors = $theme->colors;
            $defaultColors = $theme->is_dark
                ? config('common.themes.dark')
                : config('common.themes.light');

            // theme was already migrated
            if (isset($oldColors['--be-disabled-bg-opacity'])) {
                return;
            }

            $newColors['--be-foreground-base'] =
                $this->colorToPartialRgb($oldColors['--be-foreground-base']) ??
                $defaultColors['--be-foreground-base'];

            $newColors['--be-primary-light'] =
                $this->colorToPartialRgb($oldColors['--be-accent-lighter']) ??
                $defaultColors['--be-primary-light'];

            $newColors['--be-primary'] =
                $this->colorToPartialRgb($oldColors['--be-accent-default']) ??
                $defaultColors['--be-primary'];

            $newColors['--be-primary-dark'] =
                $this->colorToPartialRgb(
                    $oldColors['--be-primary-darker'],
                    -0.1,
                ) ?? $defaultColors['--be-primary-dark'];

            $newColors['--be-on-primary'] =
                $this->colorToPartialRgb($oldColors['--be-accent-contrast']) ??
                $defaultColors['--be-on-primary'];

            $newColors['--be-background'] =
                $this->colorToPartialRgb($oldColors['--be-background']) ??
                $defaultColors['--be-background'];

            $newColors['--be-background-alt'] =
                $this->colorToPartialRgb(
                    $oldColors['--be-background-alternative'],
                ) ?? $defaultColors['--be-background-alt'];

            $newColors['--be-background-chip'] =
                $this->colorToPartialRgb($oldColors['--be-chip']) ??
                $defaultColors['--be-background-chip'];

            $newColors['--be-paper'] =
                $this->colorToPartialRgb($oldColors['--be-background']) ??
                $defaultColors['--be-paper'];

            $newColors['--be-disabled-bg-opacity'] =
                $defaultColors['--be-disabled-bg-opacity'];
            $newColors['--be-disabled-fg-opacity'] =
                $defaultColors['--be-disabled-fg-opacity'];
            $newColors['--be-hover-opacity'] =
                $defaultColors['--be-hover-opacity'];
            $newColors['--be-focus-opacity'] =
                $defaultColors['--be-focus-opacity'];
            $newColors['--be-selected-opacity'] =
                $defaultColors['--be-selected-opacity'];
            $newColors['--be-text-main-opacity'] =
                $defaultColors['--be-text-main-opacity'];
            $newColors['--be-text-muted-opacity'] =
                $defaultColors['--be-text-muted-opacity'];
            $newColors['--be-divider-opacity'] =
                $defaultColors['--be-divider-opacity'];

            $theme->colors = $newColors;
            $theme->save();
        });
    }

    function colorToPartialRgb(
        mixed $colorString,
        float $brightness = 0,
    ): string|null {
        try {
            $rgb = Factory::fromString($colorString)->toRgb();
            if ($brightness !== 0) {
                $rgb = Hex::fromString(
                    $this->colourBrightness($rgb->toHex(), $brightness),
                )->toRgb();
            }
            return "{$rgb->red()} {$rgb->green()} {$rgb->blue()}";
        } catch (Exception) {
            return null;
        }
    }

    function colourBrightness(string $hex, float $percent): string
    {
        // Work out if hash given
        $hash = '';
        if (stristr($hex, '#')) {
            $hex = str_replace('#', '', $hex);
            $hash = '#';
        }
        /// HEX TO RGB
        $rgb = [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
        //// CALCULATE
        for ($i = 0; $i < 3; $i++) {
            // See if brighter or darker
            if ($percent > 0) {
                // Lighter
                $rgb[$i] =
                    round($rgb[$i] * $percent) + round(255 * (1 - $percent));
            } else {
                // Darker
                $positivePercent = $percent - $percent * 2;
                $rgb[$i] = round($rgb[$i] * (1 - $positivePercent)); // round($rgb[$i] * (1-$positivePercent));
            }
            // In case rounding up causes us to go to 256
            if ($rgb[$i] > 255) {
                $rgb[$i] = 255;
            }
        }
        //// RBG to Hex
        $hex = '';
        for ($i = 0; $i < 3; $i++) {
            // Convert the decimal digit to hex
            $hexDigit = dechex($rgb[$i]);
            // Add a leading zero if necessary
            if (strlen($hexDigit) == 1) {
                $hexDigit = '0' . $hexDigit;
            }
            // Append to the hex string
            $hex .= $hexDigit;
        }
        return $hash . $hex;
    }

    /**
     * Convert "ads.some.slot" to "ads.some_slot"
     */
    protected function migrateAds()
    {
        $settings = Setting::where('name', 'like', 'ads.%')->get();
        $settings->each(function (Setting $setting) {
            if (substr_count($setting->name, '.') > 1) {
                $slot = str_replace('ads.', '', $setting->name);
                $setting->name = 'ads.' . str_replace('.', '_', $slot);
                $setting->save();
            }
        });
    }

    public function down()
    {
    }
};
