import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { PermissionService } from '../../../core/services/permission.service'; // Adjust path if necessary
import { RoleService } from '../../../core/services/role.service';
import { Role, Permission } from '../../../core/models/role.model'; // Asegúrate de que Role y Permission están bien definidos

@Component({
  selector: 'app-role-create', // Ensure this selector is correct
  templateUrl: './create.component.html', // Ensure this HTML file exists
  styleUrls: ['./create.component.css'],
})
export class CreateComponent implements OnInit {
  roleForm!: FormGroup; // Declare roleForm as a FormGroup
  permissions: Permission[] = []; // Declare permissions array to hold all available permissions
  selectedPermissionIds: number[] = []; // Declare array to hold selected permission IDs
  // Variable para controlar la visibilidad del JSON de depuración
  showDebugJson: boolean = false;
  constructor(
    private fb: FormBuilder,
    private roleService: RoleService,
    private permissionService: PermissionService, // Inyecta PermissionService
    private router: Router
  ) {}

  ngOnInit(): void {
    // Initialize the form group with 'name' and 'guard_name' controls
    this.roleForm = this.fb.group({
      name: ['', Validators.required],
      guard_name: ['web'], // Añade guard_name con un valor por defecto
    });
    this.loadPermissions(); // Load all available permissions for the checkboxes
  }

  /**
   * Loads all available permissions from the PermissionService.
   */
  loadPermissions(): void {
    // Asegúrate de que tu PermissionService tiene un método 'getAll'
    // Si tu servicio devuelve un objeto con 'message' y 'data', ajusta el tipo aquí.
    this.permissionService.getPermissions().subscribe({ // <-- Asumo que el método es getPermissions() como en RoleService
      next: (response: { message: string; data: Permission[] }) => { // <--- Ajusta el tipo de respuesta si es necesario
        this.permissions = response.data;
      },
      error: (err) => {
        console.error('Error loading permissions:', err);
        this.permissions = [];
      }
    });
  }

  /**
   * Handles changes to the permission checkboxes.
   * Adds or removes permission IDs from the selectedPermissionIds array.
   * @param event The change event from the checkbox.
   */
  // Ajuste: onPermissionChange debería recibir el ID del permiso y el estado 'checked' directamente,
  // o el evento completo para parsearlo. Usaremos el patrón de handlePermissionCheckboxChange
  // para mayor robustez, como en EditComponent.
  handlePermissionCheckboxChange(permissionId: number, event: Event): void {
    const isChecked = (event.target as HTMLInputElement).checked;
    this.updateSelectedPermissions(permissionId, isChecked);
  }

  /**
   * Actualiza el array de IDs de permisos seleccionados.
   * @param permissionId El ID del permiso.
   * @param isChecked El estado del checkbox.
   */
  updateSelectedPermissions(permissionId: number, isChecked: boolean): void {
    if (isChecked) {
      this.selectedPermissionIds.push(permissionId);
    } else {
      this.selectedPermissionIds = this.selectedPermissionIds.filter(id => id !== permissionId);
    }
    console.log('Permisos seleccionados:', this.selectedPermissionIds);
  }

  /**
   * Checks if a permission ID is already selected.
   * Used to set the `checked` state of the checkboxes in the template.
   * @param permissionId The ID of the permission to check.
   * @returns True if the permission is selected, false otherwise.
   */
  isPermissionSelected(permissionId: number): boolean { // Removido 'undefined' ya que `permission.id` siempre existirá
    return this.selectedPermissionIds.includes(permissionId);
  }
  toggleDebugJson(): void {
    this.showDebugJson = !this.showDebugJson;
  }
  /**
   * Handles the form submission.
   * If the form is valid, it creates a new role with the selected permissions.
   */
  onSubmit(): void {
    if (this.roleForm.valid) {
      // ¡CORRECCIÓN CLAVE AQUÍ! Define el tipo exacto del payload que se enviará.
      // Este payload es un objeto que contiene las propiedades necesarias para crear un rol,
      // incluyendo 'name', 'guard_name', y 'permissions' (como un array de números, IDs).
      const newRolePayload: { name: string; guard_name: string; permissions: number[] } = {
        name: this.roleForm.value.name,
        guard_name: this.roleForm.value.guard_name, // Asegúrate de que este campo se envíe
        permissions: this.selectedPermissionIds // Adjunta los IDs de permisos seleccionados
      };

      // Si tu createRole en RoleService espera un tipo diferente, ajústalo.
      // Asumo que createRole espera este tipo de objeto y el backend lo procesa.
      this.roleService.createRole(newRolePayload).subscribe({ // <--- Pasa el payload con el tipo correcto
        next: (response) => {
          console.log('Rol creado:', response);
          this.router.navigate(['/dashboard/roles']); // Redirige a la lista de roles
        },
        error: (err) => {
          console.error('Error al crear rol:', err);
          // Implementa una visualización de error más amigable aquí.
          // Por ejemplo, usando una variable para mostrar el mensaje en el HTML.
        },
      });
    }
  }
}
