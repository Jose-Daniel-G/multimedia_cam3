// src/app/modules/formulario-display/formulario-display.module.ts
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common'; // Necesitas CommonModule para directivas como *ngIf, *ngFor
import { PermissionRoutingModule } from './permission-routing.module';
import { PermissionsComponent } from './list-permissions/permissions.component';
import { PaginationComponent } from '../../shared1/components/pagination/pagination.component';
import { MessageComponent } from '../../shared1/components/message/message.component';
import { ReactiveFormsModule } from '@angular/forms';
import { EditComponent } from './edit/edit.component';
import { CreateComponent } from './create/create.component';



@NgModule({
  declarations: [PermissionsComponent, EditComponent, PaginationComponent, CreateComponent, MessageComponent// EnProcesoComponent
  ],
  imports: [
    CommonModule, PermissionRoutingModule,  ReactiveFormsModule,  
  ],
  exports: [
  ]
})
export class PermissionModule { }