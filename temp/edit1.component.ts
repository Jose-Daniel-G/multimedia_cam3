import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { RoleService } from '../../../core/services/role.service'; // Asegúrate de que esta ruta sea correcta
// Importa las interfaces necesarias, incluyendo PaginationData
import { Role, Permission, UpdateRolePayload, ApiResponse, PaginationData } from '../../../core/models/role.model';
import { tap } from 'rxjs/operators'; // Importa tap para depuración

@Component({
  selector: 'app-edit-role',
  standalone: false,
  templateUrl: './edit.component.html',
  styleUrls: ['./edit.component.css'],
})
export class EditComponent implements OnInit {
  editRoleForm: FormGroup;
  roleId: number | null = null;
  errorMessage: string = '';
  successMessage: string = '';

  availablePermissions: Permission[] = [];
  selectedPermissionIds: number[] = [];

  // Variable para controlar la visibilidad del JSON de depuración
  showDebugJson: boolean = false;

  constructor(
    private fb: FormBuilder,
    private route: ActivatedRoute,
    private roleService: RoleService,
    private router: Router
  ) {
    this.editRoleForm = this.fb.group({
      name: ['', Validators.required],
      guard_name: ['web', Validators.required], // guard_name debe ser requerido y tener un valor por defecto
    });
  }

  ngOnInit(): void {
    this.roleId = Number(this.route.snapshot.paramMap.get('id'));

    // Cargar todos los permisos disponibles al inicio
    this.roleService.getPermissions().subscribe({
      // ¡CORRECCIÓN AQUÍ! Ahora el servicio devuelve directamente Permission[]
      next: (permissions: Permission[]) => {
        console.log('Type of permissions received:', typeof permissions); // Debug log
        console.log('Is permissions an array?', Array.isArray(permissions)); // Debug log

        this.availablePermissions = permissions; // Accede al array de permisos directamente
        console.log('Permisos disponibles cargados:', this.availablePermissions);

        // Si el ID del rol es válido, cargar los datos del rol y preseleccionar sus permisos
        if (this.roleId) {
          this.loadRoleData(this.roleId);
        }
      },
      error: (err: any) => {
        console.error('Error al cargar permisos disponibles:', err);
        this.errorMessage = err.error?.message || 'Error al cargar los permisos disponibles.';
      }
    });
  }

  loadRoleData(id: number): void {
    this.roleService.getRole(id).subscribe({
      // Asumo que getRole() devuelve directamente un objeto Role.
      next: (role: Role) => {
        console.log('Datos del rol cargados:', role);
        this.editRoleForm.patchValue({
          name: role.name,
          guard_name: role.guard_name,
        });

        // Preselecciona los permisos que ya tiene el rol
        if (role.permissions && role.permissions.length > 0) {
          this.selectedPermissionIds = role.permissions.map(p => p.id);
          console.log('Permisos preseleccionados para el rol:', this.selectedPermissionIds);
        } else {
          this.selectedPermissionIds = [];
          console.log('El rol no tiene permisos asignados o la propiedad permissions está vacía.');
        }
      },
      error: (err: any) => {
        console.error('Error al cargar rol:', err);
        this.errorMessage = err.error?.message || 'Error al cargar el rol.';
      },
    });
  }

  // Este método se llamará cuando el usuario seleccione/deseleccione un permiso
  onPermissionChange(permissionId: number, isChecked: boolean): void {
    if (isChecked) {
      // Evita duplicados
      if (!this.selectedPermissionIds.includes(permissionId)) {
        this.selectedPermissionIds.push(permissionId);
      }
    } else {
      this.selectedPermissionIds = this.selectedPermissionIds.filter(
        (id) => id !== permissionId
      );
    }
    console.log('Permisos seleccionados:', this.selectedPermissionIds);
  }

  isPermissionSelected(permissionId: number): boolean {
    return this.selectedPermissionIds.includes(permissionId);
  }

  // Método auxiliar para manejar el evento del checkbox en el HTML
  handlePermissionCheckboxChange(permissionId: number, event: Event): void {
    const isChecked = (event.target as HTMLInputElement).checked;
    this.onPermissionChange(permissionId, isChecked);
  }

  // Nuevo método para alternar la visibilidad del JSON de depuración
  toggleDebugJson(): void {
    this.showDebugJson = !this.showDebugJson;
  }

  onSubmit(): void {
    this.errorMessage = '';
    this.successMessage = '';

    if (this.editRoleForm.invalid || !this.roleId) {
      this.errorMessage =
        'Por favor, completa todos los campos requeridos y asegúrate de que el ID del rol es válido.';
      this.editRoleForm.markAllAsTouched();
      return;
    }

    // Usa la interfaz UpdateRolePayload directamente.
    const updatedRolePayload: UpdateRolePayload = {
      name: this.editRoleForm.value.name,
      guard_name: this.editRoleForm.value.guard_name,
      // ¡Importante! Asegúrate de que tu backend espera 'permission' y no 'permissions'
      // Si tu backend espera 'permissions', deberías cambiar la interfaz UpdateRolePayload
      permission: this.selectedPermissionIds,
    };

    console.log('Enviando payload de actualización:', updatedRolePayload);

    this.roleService.updateRole(this.roleId, updatedRolePayload).subscribe({
      // Asumo que updateRole() devuelve directamente un objeto SuccessMessageResponse.
      next: (response: { message: string; data?: any }) => { // Utiliza el tipo adecuado para la respuesta
        this.successMessage = response.message || 'Rol actualizado exitosamente.';
        console.log('Respuesta de actualización de rol:', response);
        this.router.navigate(['/dashboard/roles']);
      },
      error: (err: any) => {
        console.error('Error al actualizar rol:', err);
        this.errorMessage = err.error?.message || 'Error al actualizar el rol.';
        if (err.error?.errors) {
          for (const key in err.error.errors) {
            this.errorMessage += `\n${err.error.errors[key].join(', ')}`;
          }
        }
      },
    });
  }
}
