@php
    $groups = [
        'Heroicons' => [
            'heroicon-o-home',
            'heroicon-o-user',
            'heroicon-o-users',
            'heroicon-o-cog',
            'heroicon-o-envelope',
            'heroicon-o-phone',
            'heroicon-o-map-pin',
            'heroicon-o-link',
            'heroicon-o-globe-alt',
            'heroicon-o-document-text',
            'heroicon-o-chat-bubble-left',
            'heroicon-o-bell',
            'heroicon-o-star',
            'heroicon-o-heart',
            'heroicon-o-check-circle',
            'heroicon-o-exclamation-circle',
            'heroicon-o-information-circle',
            'heroicon-o-question-mark-circle',
            'heroicon-o-arrow-right',
            'heroicon-o-arrow-left',
            'heroicon-o-arrow-up',
            'heroicon-o-arrow-down',
            'heroicon-o-chevron-right',
            'heroicon-o-chevron-left',
            'heroicon-o-chevron-up',
            'heroicon-o-chevron-down',
            'heroicon-o-plus',
            'heroicon-o-minus',
            'heroicon-o-x-mark',
            'heroicon-o-magnifying-glass',
            'heroicon-o-briefcase',
            'heroicon-o-building-office',
            'heroicon-o-academic-cap',
            'heroicon-o-book-open',
            'heroicon-o-camera',
            'heroicon-o-video-camera',
            'heroicon-o-musical-note',
            'heroicon-o-microphone',
            'heroicon-o-calendar',
            'heroicon-o-clock',
            'heroicon-o-currency-dollar',
            'heroicon-o-shopping-cart',
            'heroicon-o-paper-clip',
            'heroicon-o-tag',
            'heroicon-o-ticket',
            'heroicon-o-key',
            'heroicon-o-lock-closed',
            'heroicon-o-shield-check',
            'heroicon-o-fire',
            'heroicon-o-light-bulb',
            'heroicon-o-computer-desktop',
            'heroicon-o-device-phone-mobile',
            'heroicon-o-wifi',
            'heroicon-o-signal',
            'heroicon-o-cloud',
            'heroicon-o-sun',
            'heroicon-o-moon',
            'heroicon-o-truck',
            'heroicon-o-chart-bar',
            'heroicon-o-presentation-chart-line',
            'heroicon-o-credit-card',
            'heroicon-o-wallet',
            'heroicon-o-folder',
            'heroicon-o-inbox',
            'heroicon-o-sparkles',
            'heroicon-o-trophy',
            'heroicon-o-photo',
            'heroicon-o-play',
            'heroicon-o-pause',
            'heroicon-o-stop',
            'heroicon-o-trash',
            'heroicon-o-pencil',
            'heroicon-o-eye',
            'heroicon-o-eye-slash',
            'heroicon-o-clipboard-document',
            'heroicon-o-wrench',
            'heroicon-o-bars-3',
            'heroicon-o-squares-2x2',
            'heroicon-o-rectangle-stack',
            'heroicon-o-archive-box',
            'heroicon-o-arrow-path',
            'heroicon-o-bolt',
            'heroicon-o-cube',
            'heroicon-o-hand-thumb-up',
            'heroicon-o-hand-thumb-down',
            'heroicon-o-flag',
            'heroicon-o-gift',
            'heroicon-o-globe-americas',
            'heroicon-o-hashtag',
            'heroicon-o-map',
            'heroicon-o-megaphone',
            'heroicon-o-paint-brush',
            'heroicon-o-paper-airplane',
            'heroicon-o-power',
            'heroicon-o-printer',
            'heroicon-o-puzzle-piece',
            'heroicon-o-rocket-launch',
            'heroicon-o-scale',
            'heroicon-o-scissors',
            'heroicon-o-server',
            'heroicon-o-share',
            'heroicon-o-swatch',
            'heroicon-o-table-cells',
            'heroicon-o-user-group',
            'heroicon-o-user-plus',
            'heroicon-o-adjustments-horizontal',
            'heroicon-o-beaker',
            'heroicon-o-calculator',
            'heroicon-o-chart-pie',
            'heroicon-o-command-line',
            'heroicon-o-cpu-chip',
            'heroicon-o-document-arrow-down',
            'heroicon-o-document-arrow-up',
            'heroicon-o-document-check',
            'heroicon-o-document-duplicate',
            'heroicon-o-finger-print',
            'heroicon-o-face-smile',
            'heroicon-o-funnel',
            'heroicon-o-hand-raised',
            'heroicon-o-identification',
            'heroicon-o-language',
            'heroicon-o-lifebuoy',
            'heroicon-o-list-bullet',
            'heroicon-o-no-symbol',
            'heroicon-o-qr-code',
            'heroicon-o-receipt-percent',
            'heroicon-o-square-3-stack-3d',
            'heroicon-o-variable',
            'heroicon-o-window',
            'heroicon-o-wrench-screwdriver',
        ],
    ];

    $groupData = [];
    foreach ($groups as $groupName => $icons) {
        $prefix = match ($groupName) {
            'Heroicons' => 'heroicon-o-',
        };
        $groupData[$groupName] = collect($icons)->map(fn($icon) => [
            'name' => $icon,
            'label' => str_replace($prefix, '', $icon),
        ])->toArray();
    }
@endphp

<style>
    .icon-picker-grid {
        display: grid !important;
        grid-template-columns: repeat(8, minmax(0, 1fr)) !important;
        gap: 8px !important;
        padding: 8px 16px 16px !important;
        max-height: 50vh !important;
        overflow-y: auto !important;
    }

    .icon-picker-card {
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 4px;
        padding: 8px 2px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        cursor: pointer;
        background: transparent;
        transition: all 0.15s;
        height: 70px;
        min-width: 0;
    }

    .icon-picker-card:hover {
        border-color: #6366f1 !important;
        background: #f3f4f6;
    }

    .icon-picker-card svg {
        width: 24px !important;
        height: 24px !important;
        flex-shrink: 0;
    }

    .icon-picker-label {
        font-size: 9px;
        line-height: 1.2;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        width: 100%;
        text-align: center;
        color: #6b7280;
        min-width: 0;
    }

    .icon-picker-tabs {
        display: flex !important;
        border-bottom: 2px solid #e5e7eb;
        padding: 0 16px;
    }

    .icon-picker-tab {
        padding: 10px 20px;
        font-size: 13px;
        font-weight: 500;
        border: none;
        background: transparent;
        cursor: pointer;
        color: #9ca3af;
    }

    .icon-picker-tab.active {
        font-weight: 600;
        color: #6366f1;
        border-bottom: 2px solid #6366f1;
        margin-bottom: -2px;
    }

    .ip-hidden {
        display: none !important;
    }
</style>

<div x-data="{
    search: '',
    activeTab: 'Heroicons',
    switchTab(group) {
        this.activeTab = group;
        this.search = '';
    },
    matchSearch(label) {
        return this.search === '' || label.includes(this.search.toLowerCase());
    },
    selectIcon(name) {
        $wire.set('{{ $statePath }}', name);
        $dispatch('close-modal', { id: $el.closest('[x-ref]')?.getAttribute('x-ref') || Object.keys($wire.__instance.canonical.snapshots || {}).pop() });
        document.querySelectorAll('.fi-modal-close-btn').forEach(b => b.click());
    }
}" x-init="$nextTick(() => $el.querySelector('#icon-picker-search')?.focus())">
    {{-- Tabs --}}
    <div class="icon-picker-tabs">
        @foreach (array_keys($groups) as $index => $groupName)
            <button type="button" class="icon-picker-tab" :class="activeTab === '{{ $groupName }}' ? 'active' : ''"
                x-on:click="switchTab('{{ $groupName }}')">
                {{ $groupName }}
            </button>
        @endforeach
    </div>

    {{-- Search --}}
    <div style="padding: 12px 16px 8px;">
        <input type="search" id="icon-picker-search" placeholder="Search icons..." x-model="search"
            style="width: 100%; padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; outline: none; background: transparent;"
            class="text-gray-900 dark:text-gray-100 dark:border-gray-600">
    </div>

    {{-- Icon Grids --}}
    @foreach ($groupData as $groupName => $icons)
        <div class="icon-picker-grid" :class="activeTab !== '{{ $groupName }}' && 'ip-hidden'">
            @foreach ($icons as $icon)
                <button type="button"
                    :class="!matchSearch('{{ $icon['label'] }}') && 'ip-hidden'"
                    x-on:click="selectIcon('{{ $icon['name'] }}')"
                    class="icon-picker-card"
                    title="{{ $icon['name'] }}">
                    @svg($icon['name'], 'text-gray-600 dark:text-gray-400')
                    <span class="icon-picker-label">{{ $icon['label'] }}</span>
                </button>
            @endforeach
        </div>
    @endforeach
</div>
