export const corrections = [
  {
    id: 1,
    title: 'Zmiana numeru telefonu w nagłówku',
    description: 'Należy podmienić numer telefonu na wszystkich podstronach.',
    page: 'Strona główna',
    pageUrl: 'https://example.com/',
    status: 'new',
    createdAt: '2026-09-12T07:30:00',
    comments: [
      {
        id: 1,
        author: 'Klient',
        content: 'Nowy numer telefonu przesłałam w wiadomości.',
        createdAt: '2026-09-12T08:10:00',
      },
      {
        id: 2,
        author: 'Natalia',
        content: 'Dziękuję, podmienię go również w stopce.',
        createdAt: '2026-09-12T08:25:00',
      },
    ],
  },
  {
    id: 2,
    title: 'Poprawienie odstępów w sekcji kontaktowej',
    description: 'Na urządzeniach mobilnych odstęp nad formularzem jest zbyt duży.',
    page: 'Kontakt',
    pageUrl: 'https://example.com/kontakt/',
    status: 'inProgress',
    createdAt: '2026-09-11T15:20:00',
    comments: [
      {
        id: 1,
        author: 'Klient',
        content: 'Problem jest najbardziej widoczny na telefonie.',
        createdAt: '2026-09-11T16:00:00',
      },
    ],
  },
  {
    id: 3,
    title: 'Podmiana zdjęcia w sekcji hero',
    description: 'Nowe zdjęcie znajduje się w materiałach przesłanych przez klienta.',
    page: 'O nas',
    pageUrl: 'https://example.com/o-nas/',
    status: 'ready',
    createdAt: '2026-09-10T10:15:00',
    comments: [],
  },
]
