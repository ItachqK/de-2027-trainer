@php
  $map = [
      'new' => 'is-new',
      'assigned' => 'is-assigned',
      'done' => 'is-done',
  ];
@endphp
<span class="status {{ $map[$status] ?? '' }}">{{ $label }}</span>
