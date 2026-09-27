🎯 OBJETIVO: Implementar Feature Flags Tracking no Frontend

📋 CONTEXTUALIZAÇÃO:
- Todos os modules core funcionando
- Necessário: Controle de sidebar e visibilidade por role/permissions
- Integração backend (funcionalidades[]) ↔ frontend (AppShell.vue)

🔧 TASKS EXECUTÁVEIS:

1. Adicionar accessors no modelo User (app/Models/User.php)
   ```
   public function getFuncionalidadesAttribute($value)
   {
       return $value ?? [];
   }
   
   public function setFuncionalidadesAttribute($value)
   {
       $this->attributes['funcionalidades'] = is_array($value) ? $value : json_decode($value, true);
   }
   ```

2. Atualizar DatabaseSeeder (database/seeders/DatabaseSeeder.php)
   ```
   public function run(): void
   {
       // Admin - Todas as funcionalidades
       $admin = User::where('role', 'admin')->first();
       $admin->funcionalidades = [
           'play-fake', 'qr-code', 'bypass', 'inject-bancos', 
           'consulta', 'ranking', 'admin', 'users-management', 'settings'
       ];
       $admin->save();
       
       // Revendedor - Funcionalidades limitadas
       $revendedor = User::where('role', 'revendedor')->first();
       $revendedor->funcionalidades = [
           'play-fake', 'qr-code', 'consulta', 'ranking'
       ];
       $revendedor->save();
       
       // Usuário - Funcionalidades básicas
       $usuario = User::where('role', 'user')->first();
       $usuario->funcionalidades = [
           'play-fake', 'qr-code', 'consulta'
       ];
       $usuario->save();
   }
   ```

3. Integrar no frontend AppShell.vue
   No frontend/frontend/src/AppShell.vue:
   ```
   <script setup>
   import { useUserStore } from '@/stores/user store';
   
   const sidebarItems = [
       { title: 'Início', path: '/', requiredFeature: null },
       { title: 'Play Fake', path: '/play-fake', requiredFeature: 'play-fake' },
       { title: 'QR Code', path: '/qr', requiredFeature: 'qr-code' },
       { title: 'Bypass', path: '/bypass', requiredFeature: 'bypass' },
       { title: 'Inject Bancos', path: '/inject-bancos', requiredFeature: 'inject-bancos' },
       { title: 'Consulta', path: '/consulta', requiredFeature: 'consulta' },
       { title: 'Ranking', path: '/ranking', requiredFeature: 'ranking' },
       { title: 'Admin', path: '/admin', requiredFeature: 'admin', showIf: 'isAdmin' },
       // ... mais itens com requiredFeature conforme necessário
   ];
   
   const userStore = useUserStore();
   const user = userStore.user;
   </script>
   
   <template>
       <b-sidebar id="sidebar" collapseable="lg" state="collapsed">
           <b-sidebar-nav>
               <b-nav-item v-for="item in filteredItems" :key="item.title">
                   <router-link :to="item.path">{{ item.title }}</router-link>
               </b-nav-item>
           </b-sidebar-nav>
       </b-sidebar>
   </template>
   
   <script setup>
   const filteredItems = sidebarItems.filter(item => {
       // Se não tem requiredFeature, mostra sempre
       if (!item.requiredFeature) return true;
       
       // Se tem requiredFeature, verificar se usuário tem
       if (user.funcionalidades.includes(item.requiredFeature)) {
           // Se tem showIf (função), chamar essa função
           if (item.showIf && typeof item.showIf === 'function') {
               return item.showIf(user.funcionalidades);
           }
           return true;
       }
       
       return false;
   });
   </script>
   ```

✅ CRITÉRIO DE ACEITE ETAPA 5:
- [ ] Model User tem accessors getFuncionalidadesAttribute/setFuncionalidadesAttribute
- [ ] Seeders populando funcionalidades diferentes por role
- [ ] AppShell.vue filtrando sidebar por funcionalidades[]
- [ ] Sidebar diferente para admin vs revendedor vs user