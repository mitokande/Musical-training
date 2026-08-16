{{-- Renders a pre-built Email Center HTML document (EmailTemplateLibrary::standaloneHtml)
     as a notification mail body, so transactional notifications share the exact
     brand shell used by Email Center instead of Laravel's stock markdown theme. --}}
{!! $html !!}
