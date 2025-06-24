// src/app/modules/formulario-display/formulario-display.module.ts
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common'; // Necesitas CommonModule para directivas como *ngIf, *ngFor
import { PermissionRoutingModule } from './permission-routing.module';
import { PermissionsComponent } from './list-permissions/permissions.component';
import { ReactiveFormsModule } from '@angular/forms';
import { EditComponent } from './edit/edit.component';
import { CreateComponent } from './create/create.component';
import { SharedModule } from '../../shared/shared.module';



@NgModule({
  declarations: [PermissionsComponent, EditComponent, CreateComponent// EnProcesoComponent
  ],
  imports: [
    CommonModule, PermissionRoutingModule,  ReactiveFormsModule,  SharedModule
  ],
  exports: [
  ]
})
export class PermissionModule { }