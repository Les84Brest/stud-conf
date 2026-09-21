<x-filament-panels::page>
    <form wire:submit.prevent>
        {{ $this->form }}
    </form>

    @if ($this->eventId)
        {{ $this->table }}
    @else
        <x-filament::section>
            <div class="text-center py-8 text-gray-500">
                Выберите конференцию и мероприятие, чтобы увидеть сводную ведомость
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>