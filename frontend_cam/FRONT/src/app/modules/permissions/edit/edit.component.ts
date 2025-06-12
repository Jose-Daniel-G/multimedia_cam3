import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { PermissionService, Permission } from '../../../core/services/permission.service';

@Component({
  selector: 'app-edit',
  templateUrl: './edit.component.html',
  styleUrls: ['./edit.component.css']
})
export class EditComponent implements OnInit {
  permissionForm!: FormGroup;
  paginatedPermissions: Permission[] = [];

  constructor(
    private fb: FormBuilder,
    private permissionService: PermissionService // ✅ Inyección correcta
  ) {}

  ngOnInit(): void {
    this.permissionForm = this.fb.group({
      name: ['', Validators.required]
    });

    // Simulación: carga inicial de un permiso
    const permiso = { name: 'Editar Usuarios' };
    this.permissionForm.patchValue(permiso);

    // Cargar lista simulada de permisos
    this.permissionService.getAll().subscribe(data => {
      this.paginatedPermissions = data;
    });
  }

  get name() {
    return this.permissionForm.get('name')!;
  }

  onSubmit(): void {
    if (this.permissionForm.valid) {
      console.log('Formulario enviado:', this.permissionForm.value);
      // Lógica para actualizar, por ejemplo:
      // this.permissionService.update(id, this.permissionForm.value).subscribe(...)
    }
  }

  deletePermission(id: number): void {
    if (confirm('¿Estás seguro de eliminar este permiso?')) {
      this.permissionService.delete(id).subscribe(() => {
        this.paginatedPermissions = this.paginatedPermissions.filter((p: Permission) => p.id !== id);
      });
    }
  }
}
