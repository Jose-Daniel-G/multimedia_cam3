import { NgModule } from '@angular/core';
import { CommonModule, NgClass, NgFor, NgIf } from '@angular/common';
import { IndexComponent } from './index/index.component';
import { EditComponent } from './edit/edit.component';
import { CreateComponent } from './create/create.component';
import { RolesRoutingModule } from './roles-routing.module';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { SharedModule } from '../../shared/components/shared.module';



@NgModule({
  declarations: [IndexComponent, EditComponent, CreateComponent  ],
  imports: [
    CommonModule, RolesRoutingModule,ReactiveFormsModule, FormsModule, NgFor,NgIf,NgClass, SharedModule 
  ]
})
export class RolesModule { }
