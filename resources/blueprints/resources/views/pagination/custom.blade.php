@if ($paginator->hasPages())
  <nav aria-label="Страницы">
    <ul class="pagination">
      @foreach ($elements as $element)
        @if (is_string($element))
          <li class="is-disabled"><span>{{ $element }}</span></li>
        @endif

        @if (is_array($element))
          @foreach ($element as $page => $url)
            @if ($page == $paginator->currentPage())
              <li class="is-active" aria-current="page"><span>{{ $page }}</span></li>
            @else
              <li><a href="{{ $url }}" aria-label="Страница {{ $page }}">{{ $page }}</a></li>
            @endif
          @endforeach
        @endif
      @endforeach
    </ul>
  </nav>
@endif
