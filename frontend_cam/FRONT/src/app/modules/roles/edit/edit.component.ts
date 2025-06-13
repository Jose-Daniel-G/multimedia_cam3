// src/app/modules/roles/edit/role-edit.component.ts
import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { PermissionService } from '../../../core/services/permission.service';
import { Permission } from '../../../core/models/permission.model';
import { ActivatedRoute, Router } from '@angular/router'; // For getting route params and navigation
import { RoleService } from '../../../core/services/role.service';
import { Role } from '../../../core/models/role.model'; // CRITICAL: Ensure Role is imported

@Component({
  selector: 'app-role-edit',
  templateUrl: './edit.component.html',
  styleUrls: ['./edit.component.css'] // Create this file if you need specific styles
})
export class EditComponent implements OnInit {
  editRoleForm!: FormGroup;
  roleId!: number; // To store the ID of the role being edited
  permissions: Permission[] = []; // To store all available permissions
  selectedPermissionIds: number[] = []; // To store the IDs of permissions currently assigned to the role

  constructor(
    private fb: FormBuilder,
    private roleService: RoleService,
    private permissionService: PermissionService,
    private route: ActivatedRoute, // To access route parameters (e.g., role ID)
    private router: Router // To navigate after update
  ) {}

  ngOnInit(): void {
    // Initialize the form group with a 'name' control
    this.editRoleForm = this.fb.group({
      name: ['', Validators.required],
    });

    // Get the role ID from the route parameters
    this.route.params.subscribe(params => {
      this.roleId = +params['id']; // The '+' converts the string ID to a number
      if (this.roleId) {
        this.loadRole(this.roleId); // Load the specific role's data
      }
    });

    this.loadPermissions(); // Load all available permissions
  }

  /**
   * Loads the details of the role to be edited and populates the form.
   * @param id The ID of the role.
   */
  loadRole(id: number): void {
    this.roleService.getRoleById(id).subscribe({
      next: (role: Role) => {
        this.editRoleForm.patchValue({
          name: role.name // Set the 'name' field in the form
        });
        // Assuming role.permissions is an array of permission IDs or objects with an 'id' property
        if (role.permissions) {
          // Map to just the IDs, handling both Permission objects and numbers
          this.selectedPermissionIds = (role.permissions as (Permission | number)[]).map(p =>
            typeof p === 'object' ? p.id : p
          );
        }
      },
      error: (err) => {
        console.error('Error loading role:', err);
        // Handle error, e.g., redirect to a 404 page or show an error message
      }
    });
  }

  /**
   * Loads all available permissions from the PermissionService.
   */
  loadPermissions(): void {
    this.permissionService.getAll().subscribe({
      next: (data: Permission[]) => {
        this.permissions = data;
      },
      error: (err) => {
        console.error('Error loading permissions:', err);
        // Handle error, e.g., show a message to the user
      }
    });
  }

  /**
   * Handles changes to the permission checkboxes.
   * Adds or removes permission IDs from the selectedPermissionIds array.
   * @param event The change event from the checkbox.
   */
  onPermissionChange(event: Event): void {
    const target = event.target as HTMLInputElement;
    const permissionId = parseInt(target.value, 10); // Ensure the value is a number

    if (target.checked) {
      this.selectedPermissionIds.push(permissionId);
    } else {
      this.selectedPermissionIds = this.selectedPermissionIds.filter(id => id !== permissionId);
    }
  }

  /**
   * Checks if a permission ID is currently selected for the role.
   * Used to set the `checked` state of the checkboxes.
   * @param permissionId The ID of the permission to check.
   * @returns True if the permission is selected, false otherwise.
   */
  isPermissionSelected(permissionId?: number): boolean {
    if (permissionId === undefined) {
      return false;
    }
    return this.selectedPermissionIds.includes(permissionId);
  }

  /**
   * Handles the form submission for updating the role.
   */
  onSubmit(): void {
    if (this.editRoleForm.valid) {
      const updatedRole: Role = {
        id: this.roleId, // CRITICAL: Include the ID for the update operation
        name: this.editRoleForm.value.name,
        // The 'permissions' property now correctly aligns with (Permission | number)[] due to Role model update
        permissions: this.selectedPermissionIds
      };

      this.roleService.updateRole(this.roleId, updatedRole).subscribe({
        next: (response) => {
          console.log('Rol actualizado:', response);
          // Redirect to the roles list or show a success message
          this.router.navigate(['/permissions']); // Adjust to your actual roles list route
        },
        error: (err) => {
          console.error('Error al actualizar rol:', err);
          // Show error message to the user
        }
      });
    }
  }
}
