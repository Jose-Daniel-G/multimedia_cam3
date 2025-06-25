import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { RoleService } from '../../../core/services/role.service';
import { Role, Permission, UpdateRolePayload, ApiResponse, SuccessMessageResponse } from '../../../core/models/role.model'; // Removed PaginationData as ApiResponse is flat now
import { ModalService } from '../../../shared/services/modal.service'; // Import ModalService for consistency with IndexComponent
import { tap } from 'rxjs/operators'; // Import tap for debugging

@Component({
  selector: 'app-edit-role',
  standalone: false,
  templateUrl: './edit.component.html', // THIS IS THE CORRECT TEMPLATE FOR EditComponent
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
    private router: Router,
    private modalService: ModalService // Inject ModalService
  ) {
    this.editRoleForm = this.fb.group({
      name: ['', Validators.required],
      guard_name: ['web', Validators.required],
    });
  }

  ngOnInit(): void {
    this.roleId = Number(this.route.snapshot.paramMap.get('id'));

    // Cargar todos los permisos disponibles al inicio
    // roleService.getPermissions now correctly returns ApiResponse<Permission[]> (where ApiResponse contains pagination)
    this.roleService.getPermissions().subscribe({
      next: (response: ApiResponse<Permission[]>) => { // Corrected to expect ApiResponse<Permission[]> based on backend structure
        this.availablePermissions = response.data.data || []; // Access permissions from response.data.data
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
      next: (role: Role) => { // Assuming getRole() returns a single Role object directly
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

  onPermissionChange(permissionId: number, isChecked: boolean): void {
    if (isChecked) {
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

  handlePermissionCheckboxChange(permissionId: number, event: Event): void {
    const isChecked = (event.target as HTMLInputElement).checked;
    this.onPermissionChange(permissionId, isChecked);
  }

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

    const updatedRolePayload: UpdateRolePayload = {
      name: this.editRoleForm.value.name,
      guard_name: this.editRoleForm.value.guard_name,
      permission: this.selectedPermissionIds,
    };

    console.log('Enviando payload de actualización:', updatedRolePayload);

    this.roleService.updateRole(this.roleId, updatedRolePayload).subscribe({
      next: (response: SuccessMessageResponse) => { // Correct type
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
