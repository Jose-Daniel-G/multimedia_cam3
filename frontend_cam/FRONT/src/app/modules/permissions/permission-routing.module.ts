import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
// import { EnProcesoComponent } from './en-proceso/en-proceso.component';
import { PermissionsComponent } from './list-permissions/permissions.component';

const routes: Routes = [
  { path: '', component: PermissionsComponent },
  // { path: 'en-proceso', component: EnProcesoComponent,  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class PermissionRoutingModule { }
