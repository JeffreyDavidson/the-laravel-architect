Name: {{ $senderName }}
Email: {{ $senderEmail }}
Type: {{ $contactType }}
Budget: {{ $budget ?? 'Not specified' }}
Project: {{ $projectTitle ?? 'Not specified' }}

Message:
{{ $contactMessage }}
