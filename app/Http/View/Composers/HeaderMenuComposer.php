<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Str;
use App\Models\Dimension\Jurusan;

class HeaderMenuComposer
{
    public function compose(View $view)
    {
        // Build the menu here so blade stays clean
        $jurusanList = Jurusan::inRandomOrder()->get()->map(function ($item) {
            return (object) [
                'id'         => $item->kontenjurusan_id,
                'alias'      => Str::lower($item->alias_jurusan),
                'name'       => $item->nama_jurusan,
                'slicedName' => Str::trim(Str::replace('Jurusan', '', $item->nama_jurusan)),
            ];
        });

        $menu = [
            [
                'name'  => 'Beranda',
                'route' => route('frontend.home'),
            ]
        ];

        $view->with('menu', $menu);
    }
}
