<?php

namespace App\Support\AdminLte;

use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use JeroenNoten\LaravelAdminLte\Menu\Filters\FilterInterface;

class UnreadMessagesBadgeFilter implements FilterInterface
{
    public function transform($item)
    {
        if (($item['route'] ?? null) !== 'admin.messages.index') {
            return $item;
        }

        if (! Auth::guard('web')->check() || ! Schema::hasTable('messages')) {
            return $item;
        }

        $unread = Message::where('sender_type', 'client')->where('is_read', false)->count();

        if ($unread > 0) {
            $item['label'] = $unread;
            $item['label_color'] = 'danger';
        }

        return $item;
    }
}
