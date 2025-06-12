import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
// import { EnProcesoComponent } from './en-proceso/en-proceso.component';
import { PermissionsComponent } from './list-permissions/permissions.component';
import { EditComponent } from './edit/edit.component';

const routes: Routes = [
  { path: '', component: PermissionsComponent },
   { path: ':id/editar', component: EditComponent }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class PermissionRoutingModule { }
