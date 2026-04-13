<x-mail::message>
    # Підтвердження реєстрації

    Ви успішно зареєстровані на подію **[{{ $data->event->name }}]({{ $eventUrl }}).

    @if(isset($data->tickets))
        Ваші квитки у вкладенні.
    @endif

    Дякуємо,<br>
    {{ config('app.name') }}
</x-mail::message>
