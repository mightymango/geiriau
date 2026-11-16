<?php

namespace App\NativeLayouts;

use Native\Mobile\Edge\Layouts\Builders\Tab;
use Native\Mobile\Edge\Layouts\Builders\TabBar;
use Native\Mobile\Edge\Layouts\NativeLayout;
use Native\Mobile\Edge\NativeComponent;

class AppLayout extends NativeLayout
{
    public function tabBar(NativeComponent $screen): ?TabBar
    {
        return TabBar::make()
            ->add(Tab::link('Heddiw', '/', ios: 'house', android: 'home'))
            ->add(Tab::link('Ymarfer', '/study', ios: 'rectangle.stack', android: 'style'))
            ->add(Tab::link('Geiriau', '/words', ios: 'book', android: 'menu_book'))
            ->add(Tab::link('Gosodiadau', '/settings', ios: 'gearshape', android: 'settings'))
            ->activeColor(theme('primary'));
    }
}
