import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router'; // Elimina RouterModule si no lo usas directamente en @Component imports
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
// No importar CommonModule, ReactiveFormsModule, RouterModule aquí si standalone es false
// y el NgModule contenedor los provee.

// Importa MessageComponent si es standalone y SharedModule lo exporta
import { RoleService } from '../../../core/services/role.service';
import { Role, Permission} from '../../../core/models/role.model';

@Component({
  selector: 'app-edit-role',
  standalone: false, // Confirmado: NO ES STANDALONE
  templateUrl: './edit.component.html',
  styleUrls: ['./edit.component.css'],
})
export class EditComponent implements OnInit {
  editRoleForm: FormGroup;
  roleId: number | null = null;
  errorMessage: string = '';
  successMessage: string = '';

  // Lista de todos los permisos disponibles (se carga por separado)
  availablePermissions: Permission[] = [];
  // IDs de permisos seleccionados para este rol (se carga y se gestiona aparte)
  selectedPermissionIds: number[] = [];

  constructor(
    private fb: FormBuilder,
    private route: ActivatedRoute,
    private roleService: RoleService,
    private router: Router
  ) {
    this.editRoleForm = this.fb.group({
      name: ['', Validators.required],
      guard_name: ['web'],
      // Ya no necesitas un control de formulario para 'permissions' si los manejas aparte
      // y solo envías los IDs en el update.
    });
  }

  ngOnInit(): void {
    this.roleId = Number(this.route.snapshot.paramMap.get('id'));
    if (this.roleId) {
      this.loadRoleData(this.roleId);
      // **IMPORTANTE:** Aquí deberías llamar a un servicio para cargar
      // 1. Todos los permisos disponibles: this.permissionService.getAllPermissions().subscribe(...)
      // 2. Los permisos específicos de este rol: this.roleService.getPermissionsForRole(this.roleId).subscribe(...)
      // Con esos datos, podrías pre-seleccionar los checkboxes de permisos en el HTML.
    }
  }

  loadRoleData(id: number): void {

  }

  // Este método se llamará cuando el usuario seleccione/deseleccione un permiso
  onPermissionChange(permissionId: number, isChecked: boolean): void {
    if (isChecked) {
      this.selectedPermissionIds.push(permissionId);
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
    // Aseguramos que event.target es un HTMLInputElement para acceder a .checked
    const isChecked = (event.target as HTMLInputElement).checked;
    this.onPermissionChange(permissionId, isChecked); // Llama al método original
  }

  onSubmit(): void {
 
  }
}
