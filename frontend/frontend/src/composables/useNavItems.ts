export type NavItem = {
  label: string;
  to: string;
  flag?: string;
  color: string;
  icon: string;
};

export type NavGroup = {
  eyebrow: string;
  items: NavItem[];
};

export const navUser: NavGroup[] = [
  {
    eyebrow: 'CONTA',
    items: [
      { label: 'Minha conta', to: '/dashboard', color: '#38bdf8', icon: 'user' },
    ]
  },
  {
    eyebrow: 'PUBLICAÇÃO',
    items: [
      { label: 'Meus Apps',   to: '/play-fake',    flag: 'play-fake',  color: '#38bdf8', icon: 'package' },
    ]
  },
  {
    eyebrow: 'FERRAMENTAS',
    items: [
      { label: 'Bypass',       to: '/bypass',       flag: 'bypass',      color: '#fb923c', icon: 'bypass' },
      { label: 'QR Code',      to: '/qrcode',       flag: 'qr-code',     color: '#34d399', icon: 'qrcode' },
      { label: 'Fura Banco',   to: '/fura-banco',   flag: 'fura-banco',  color: '#a855f7', icon: 'inject' },
      { label: 'Consulta',     to: '/consulta',     flag: 'consulta',    color: '#f5a623', icon: 'search' },
      { label: 'Top Rank',     to: '/top-rank',     flag: 'ranking',     color: '#f5a623', icon: 'toprank' },
    ]
  },
  {
    eyebrow: 'CONTEÚDO',
    items: [
      { label: 'Tutoriais',    to: '/tutoriais',     flag: 'tutoriais',   color: '#fb7185', icon: 'tutorial' },
      { label: 'Tela Preta',   to: '/templates',     flag: 'screen-troll', color: '#34d399', icon: 'template' },
      { label: 'Inject Bancos', to: '/inject-bancos', flag: 'inject-banco', color: '#a855f7', icon: 'inject' },
    ]
  },
  {
    eyebrow: 'RANKING',
    items: [
      { label: 'Ranking — pódio', to: '/ranking', flag: 'ranking', color: '#f5a623', icon: 'toprank' },
    ]
  },
  {
    eyebrow: 'AJUDA',
    items: [
      { label: 'Suporte', to: '/suporte', color: '#2dd4bf', icon: 'support' },
    ]
  },
];

export const navAdmin: NavGroup[] = [
  {
    eyebrow: 'PAINEL',
    items: [
      { label: 'Visão geral',     to: '/admin/dashboard',          color: '#38bdf8', icon: 'dashboard' },
      { label: 'Financeiro',      to: '/admin/financeiro',        color: '#34d399', icon: 'package' },
      { label: 'Serviços',        to: '/admin/servicos',          color: '#a855f7', icon: 'settings' },
    ]
  },
  {
    eyebrow: 'CONTAS',
    items: [
      { label: 'Usuários',         to: '/admin/usuarios',          flag: 'users-management', color: '#818cf8', icon: 'users' },
      { label: 'Novo Usuário',     to: '/admin/usuarios/novo',     flag: 'users-management', color: '#818cf8', icon: 'user-plus' },
      { label: 'Revendedores',     to: '/admin/revendedores',      flag: 'admin', color: '#f5a623', icon: 'reseller' },
      { label: 'Planos',           to: '/admin/planos',            flag: 'settings', color: '#f5a623', icon: 'plan' },
      { label: 'Novo Plano',       to: '/admin/planos/novo',       flag: 'settings', color: '#f5a623', icon: 'plan-plus' },
    ]
  },
  {
    eyebrow: 'PLAY FAKE',
    items: [
      { label: 'Apps',             to: '/admin/apps',              flag: 'play-fake', color: '#38bdf8', icon: 'package' },
    ]
  },
  {
    eyebrow: 'CONTEÚDO',
    items: [
      { label: 'Tela Preta',        to: '/admin/templates',        flag: 'screen-troll', color: '#34d399', icon: 'template' },
      { label: 'Inject Bancos',     to: '/fura-banco',             flag: 'fura-banco',   color: '#a855f7', icon: 'inject' },
      { label: 'Tutoriais',         to: '/admin/tutoriais',        flag: 'admin', color: '#fb7185', icon: 'tutorial' },
      { label: 'Novo Tutorial',     to: '/admin/tutoriais/novo',   flag: 'admin', color: '#fb7185', icon: 'tutorial-plus' },
    ]
  },
  {
    eyebrow: 'SISTEMA',
    items: [
      { label: 'Gestão Top Rank',      to: '/admin/top-rank',         flag: 'ranking', color: '#f5a623', icon: 'toprank' },
      { label: 'Ranking — pódio',      to: '/admin/top-rank',       flag: 'ranking', color: '#f5a623', icon: 'toprank' },
      { label: 'Tela de Encerramento', to: '/admin/sistema/tela-encerramento', flag: 'admin', color: '#fb7185', icon: 'shield' },
      { label: 'Logs',                  to: '/admin/logs',              flag: 'admin', color: '#fb7185', icon: 'logs' },
      { label: 'Suporte',               to: '/admin/suporte',           flag: 'admin', color: '#2dd4bf', icon: 'support' },
    ]
  },
];

export const navRevendedor: NavGroup[] = [
  {
    eyebrow: 'GESTÃO',
    items: [
      { label: 'Meus Usuários',    to: '/revendedor/usuarios',   flag: 'consulta', color: '#818cf8', icon: 'users' },
      { label: 'Novo Usuário',     to: '/revendedor/usuarios/novo', flag: 'consulta', color: '#818cf8', icon: 'user-plus' },
      { label: 'Apps dos clientes', to: '/revendedor/apps',       flag: 'play-fake', color: '#38bdf8', icon: 'package' },
      { label: 'Top Rank',          to: '/revendedor/top-rank',  flag: 'ranking', color: '#f5a623', icon: 'toprank' },
      { label: 'Ranking — pódio',   to: '/revendedor/top-rank',   flag: 'ranking', color: '#f5a623', icon: 'toprank' },
    ]
  },
  {
    eyebrow: 'CONTEÚDO',
    items: [
      { label: 'Tutoriais',    to: '/revendedor/tutoriais',   flag: 'tutoriais', color: '#fb7185', icon: 'tutorial' },
      { label: 'Novo Tutorial', to: '/revendedor/tutoriais/novo', flag: 'tutoriais', color: '#fb7185', icon: 'tutorial-plus' },
    ]
  },
  {
    eyebrow: 'AJUDA',
    items: [
      { label: 'Suporte', to: '/revendedor/suporte', color: '#2dd4bf', icon: 'support' },
    ]
  },
];
