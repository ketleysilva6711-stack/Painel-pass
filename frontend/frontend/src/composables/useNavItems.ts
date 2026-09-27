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
      { label: 'Play Fake',  to: '/tools/play-fake',  flag: 'play-fake',  color: '#38bdf8', icon: 'package' },
      { label: 'QR Code',    to: '/tools/qr-code',    flag: 'qr-code',    color: '#34d399', icon: 'qrcode' },
      { label: 'Bypass',     to: '/tools/bypass',     flag: 'bypass',      color: '#fb923c', icon: 'bypass' },
    ]
  },
  {
    eyebrow: 'FERRAMENTAS',
    items: [
      { label: 'Consulta',      to: '/tools/consulta',    flag: 'consulta',      color: '#f5a623', icon: 'search' },
      { label: 'Ranking — pódio', to: '/tools/ranking',   flag: 'ranking',       color: '#f5a623', icon: 'toprank' },
      { label: 'Inject Bancos', to: '/tools/inject-bancos', flag: 'inject-bancos', color: '#a855f7', icon: 'inject' },
    ]
  },
  {
    eyebrow: 'CONTEÚDO',
    items: [
      { label: 'Tutoriais', to: '/tutoriais', color: '#fb7185', icon: 'tutorial' },
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
      { label: 'Visão geral', to: '/admin/dashboard', color: '#38bdf8', icon: 'dashboard' },
    ]
  },
  {
    eyebrow: 'CONTAS',
    items: [
      { label: 'Usuários',     to: '/admin/usuarios',    flag: 'users-management', color: '#818cf8', icon: 'users' },
      { label: 'Revendedores', to: '/admin/revendedores', flag: 'admin', color: '#f5a623', icon: 'reseller' },
      { label: 'Planos',       to: '/admin/planos',      flag: 'settings', color: '#f5a623', icon: 'plan' },
    ]
  },
  {
    eyebrow: 'FINANCEIRO',
    items: [
      { label: 'Financeiro', to: '/admin/financeiro', flag: 'admin', color: '#34d399', icon: 'package' },
    ]
  },
  {
    eyebrow: 'PLAY FAKE',
    items: [
      { label: 'Apps',             to: '/admin/apps',    flag: 'play-fake', color: '#38bdf8', icon: 'package' },
      { label: 'Domínios Play Fake', to: '/admin/dominios', flag: 'admin', color: '#a855f7', icon: 'globe' },
    ]
  },
  {
    eyebrow: 'CONTEÚDO',
    items: [
      { label: 'Tela Preta',    to: '/admin/tela-preta',    flag: 'inject-bancos', color: '#34d399', icon: 'template' },
      { label: 'Inject Bancos', to: '/admin/inject-bancos', flag: 'inject-bancos', color: '#a855f7', icon: 'inject' },
      { label: 'Tutoriais',     to: '/admin/tutoriais',     flag: 'admin', color: '#fb7185', icon: 'tutorial' },
    ]
  },
  {
    eyebrow: 'SISTEMA',
    items: [
      { label: 'Gestão Top Rank',      to: '/admin/top-rank',       flag: 'ranking', color: '#f5a623', icon: 'toprank' },
      { label: 'Ranking — pódio',      to: '/admin/ranking',        flag: 'ranking', color: '#f5a623', icon: 'toprank' },
      { label: 'Tela de encerramento', to: '/admin/encerramento',   flag: 'admin', color: '#fb7185', icon: 'shield' },
      { label: 'Logs',                 to: '/admin/logs',           flag: 'admin', color: '#fb7185', icon: 'logs' },
      { label: 'Suporte',              to: '/admin/suporte',        flag: 'admin', color: '#2dd4bf', icon: 'support' },
    ]
  },
];

export const navRevendedor: NavGroup[] = [
  {
    eyebrow: 'GESTÃO',
    items: [
      { label: 'Meus usuários',    to: '/revendedor/usuarios',  flag: 'consulta', color: '#818cf8', icon: 'users' },
      { label: 'Apps dos clientes', to: '/revendedor/apps',     flag: 'play-fake', color: '#38bdf8', icon: 'package' },
      { label: 'Top Rank',         to: '/revendedor/top-rank',  flag: 'ranking', color: '#f5a623', icon: 'toprank' },
      { label: 'Ranking — pódio',  to: '/revendedor/ranking',   flag: 'ranking', color: '#f5a623', icon: 'toprank' },
    ]
  },
  {
    eyebrow: 'CONTEÚDO',
    items: [
      { label: 'Tutoriais', to: '/revendedor/tutoriais', color: '#fb7185', icon: 'tutorial' },
    ]
  },
  {
    eyebrow: 'AJUDA',
    items: [
      { label: 'Suporte', to: '/revendedor/suporte', color: '#2dd4bf', icon: 'support' },
    ]
  },
];