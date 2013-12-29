{{ $app->assets(['addons:assets/addons.js','addons:assets/js/index.js']) }}


<h1>Addons</h1>

<div data-ng-controller="addons">


    @if(count($addons))
        
        <table class="uk-table">
            <tbody>
                @foreach($addons as $addon)
                <tr>
                    <td>{{ $addon }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

    @else

        <div class="uk-alert">
            No additional addons installed.
        </div>

    @endif


</div>
