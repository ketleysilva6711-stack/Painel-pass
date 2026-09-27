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
      { label: 'Play Fake',  to: '/tools/play-fake',  flag: 'PlayFake',    color: '#38bdf8', icon: 'package' },
      { label: 'QR Code',    to: '/tools/qr-code',    flag: 'PlayFake',    color: '#34d399', icon: 'qrcode' },
      { label: 'Bypass',     to: '/tools/bypass',     flag: 'Bypass',      color: '#fb923c', icon: 'bypass' },
      { label: 'Fura Banco', to: '/tools/fura-banco',  flag: 'FuraBanco',  color: '#a855f7', icon: 'store' },
    ]
  },
  {
    eyebrow: 'FERRAMENTAS',
    items: [
      { label: 'Consulta',      to: '/tools/consulta',    flag: 'PuxadaVip',   color: '#f5a623', icon: 'search' },
      { label: 'Top Rank',      to: '/tools/top-rank',    flag: '',            color: '#f5a623', icon: 'toprank' },
      { label: 'Ranking — pódio', to: '/tools/ranking',   flag: '',            color: '#f5a623', icon: 'toprank' },
      { label: 'Tela Preta',    to: '/tools/tela-preta',  flag: 'ScreenTroll', color: '#34d399', icon: 'template' },
      { label: 'Inject Bancos', to: '/tools/inject-bancos', flag: 'InjectBanco', color: '#a855f7', icon: 'inject' },
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
      { label: 'Usuários',     to: '/admin/usuarios',    color: '#818cf8', icon: 'users' },
      { label: 'Revendedores', to: '/admin/revendedores', color: '#f5a623', icon: 'reseller' },
      { label: 'Planos',       to: '/admin/planos',      color: '#f5a623', icon: 'plan' },
    ]
  },
  {
    eyebrow: 'FINANCEIRO',
    items: [
      { label: 'Financeiro', to: '/admin/financeiro', color: '#34d399', icon: 'package' },
    ]
  },
  {
    eyebrow: 'PLAY FAKE',
    items: [
      { label: 'Apps',             to: '/admin/apps',    color: '#38bdf8', icon: 'package' },
      { label: 'Domínios Play Fake', to: '/admin/dominios', color: '#a855f7', icon: 'globe' },
    ]
  },
  {
    eyebrow: 'CONTEÚDO',
    items: [
      { label: 'Tela Preta',    to: '/admin/tela-preta',    color: '#34d399', icon: 'template' },
      { label: 'Inject Bancos', to: '/admin/inject-bancos', color: '#a855f7', icon: 'inject' },
      { label: 'Tutoriais',     to: '/admin/tutoriais',     color: '#fb7185', icon: 'tutorial' },
    ]
  },
  {
    eyebrow: 'SISTEMA',
    items: [
      { label: 'Gestão Top Rank',      to: '/admin/top-rank',       color: '#f5a623', icon: 'toprank' },
      { label: 'Ranking — pódio',      to: '/admin/ranking',        color: '#f5a623', icon: 'toprank' },
      { label: 'Tela de encerramento', to: '/admin/encerramento',   color: '#fb7185', icon: 'shield' },
      { label: 'Logs',                 to: '/admin/logs',           color: '#fb7185', icon: 'logs' },
      { label: 'Suporte',              to: '/admin/suporte',        color: '#2dd4bf', icon: 'support' },
    ]
  },
];

export const navRevendedor: NavGroup[] = [
  {
    eyebrow: 'GESTÃO',
    items: [
      { label: 'Meus usuários',    to: '/revendedor/usuarios',  color: '#818cf8', icon: 'users' },
      { label: 'Apps dos clientes', to: '/revendedor/apps',     color: '#38bdf8', icon: 'package' },
      { label: 'Top Rank',         to: '/revendedor/top-rank',  color: '#f5a623', icon: 'toprank' },
      { label: 'Ranking — pódio',  to: '/revendedor/ranking',   color: '#f5a623', icon: 'toprank' },
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