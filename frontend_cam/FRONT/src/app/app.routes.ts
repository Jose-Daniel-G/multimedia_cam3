import { Routes } from '@angular/router';
import { LayoutComponent } from './core/layout/layout.component';
import { LoginComponent } from './modules/auth/login/login.component';
import { AuthGuard } from './core/guards/auth.guard';
import { FormularioComponent } from './modules/formulario-display/formulario/formulario.component';

export const routes: Routes = [
  { path: '', redirectTo: 'login', pathMatch: 'full' }, // 🔹 Asegura que inicie en login
  { path: 'login', component: LoginComponent },
  {
    path: '',
    component: LayoutComponent,
    children: [
      {
        path: 'dashboard',
        loadChildren: () => import('./modules/dashboard/dashboard.module').then(m => m.DashboardModule),
        canActivate: [AuthGuard],
        data: { roles: ['administradorWM'] } // Solo el superAdministradorWM puede acceder
      },
      {
        path: 'procesar',
        loadChildren: () => import('./modules/procesar/procesar.module').then(m => m.ProcesarModule),
        canActivate: [AuthGuard],
        data: { roles: ['administradorWM', 'presidenteCAEC', 'tesoreroCAEC', 'presidenteCV'] }
      },
      {
        path: 'permissions',
        loadChildren: () => import('./modules/permissions/permission.module').then(m => m.PermissionModule),
        canActivate: [AuthGuard],
        data: { roles: ['administradorWM', 'presidenteCAEC', 'tesoreroCAEC', 'presidenteCV'] } // Todos pueden acceder
      },
      {
        path: 'roles',
        loadChildren: () => import('./modules/roles/roles.module').then(m => m.RolesModule),
        canActivate: [AuthGuard],
        data: { roles: ['administradorWM', 'presidenteCAEC', 'tesoreroCAEC', 'presidenteCV'] } // Todos pueden acceder
      },
      {
        path: 'registro-compras',
        loadChildren: () => import('./modules/registro-compras/registro-compras.module').then(m => m.RegistroComprasModule),
        canActivate: [AuthGuard],
        data: { roles: ['administradorWM', 'presidenteCAEC', 'tesoreroCAEC', 'presidenteCV'] } // Todos pueden acceder
      },      
      {
        path: 'formulario-completo', // Puedes elegir la URL que prefieras
        component: FormularioComponent, // Asigna directamente el componente standalone
        canActivate: [AuthGuard], // Asegúrate de que solo usuarios autenticados puedan acceder
        // Opcional: Define roles específicos si solo ciertos usuarios deben ver este formulario
        data: { roles: ['administradorWM', 'algunRolEspecificoDelFormulario'] }
      },
    ]
  },
  { path: '**', redirectTo: '/login' } // 🔹 Redirige cualquier otra URL inválida a login
];
