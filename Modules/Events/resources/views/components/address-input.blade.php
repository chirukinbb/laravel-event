<div class="address-section mb-3">
    <div class="search-container w-100">
        <x-adminlte-input name="address" value="{{ $value ?? old('address') }}"
                          placeholder="Start typing an address or city..." autocomplete="off" id="address-input"/>
    </div>
</div>

@once
    @push('js')
        <script>
            const TOMTOM_API_KEY = '{{env('TOM_TOM_GEOCODING_API_KEY')}}'

            const input = document.getElementById('address-input');

            const awesomplete = new Awesomplete(input, {
                minChars: 3,
                maxItems: 5,
                autoFirst: true,
                filter: () => true
            });

            let debounceTimer;

            input.addEventListener('input', (e) => {
                const query = e.target.value.trim();

                if (query.length < 3) return;

                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    fetchTomTomAddresses(query);
                }, 300);
            });

            async function fetchTomTomAddresses(query) {
                const url = `https://api.tomtom.com/search/2/geocode/${encodeURIComponent(query)}.json?key=${TOMTOM_API_KEY}&typeahead=true&limit=5&language=ru-RU`;

                try {
                    const response = await fetch(url);
                    const data = await response.json();

                    if (data.results && data.results.length > 0) {
                        const suggestions = data.results.map(result => result.address.freeformAddress);
                        console.log(suggestions)
                        awesomplete.list = suggestions;
                        awesomplete.evaluate();
                    }
                } catch (error) {
                    console.error('Ошибка TomTom API:', error);
                }
            }

            input.addEventListener('awesomplete-selectcomplete', (e) => {
                console.log('Выбранный адрес:', e.text.value);
            });
        </script>
    @endpush
@endonce