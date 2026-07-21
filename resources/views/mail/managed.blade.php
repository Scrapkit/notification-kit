{{-- $renderedBody is HTML produced by the package renderer: markdown compiled
     with html_input=escape and placeholder values already escaped, so no
     database content can inject markup here. --}}
<x-mail::message>
{!! $renderedBody !!}
</x-mail::message>
