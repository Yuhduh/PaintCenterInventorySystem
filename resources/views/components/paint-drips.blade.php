<div {{ $attributes->class(['paint-drips']) }} aria-hidden="true">
    @foreach (range(1, 7) as $drip)
        <span class="paint-drip"><span class="paint-drop"></span></span>
    @endforeach
</div>
