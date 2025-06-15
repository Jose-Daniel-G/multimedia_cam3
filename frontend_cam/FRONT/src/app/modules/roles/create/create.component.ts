import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { RoleService } from '../../../core/services/role.service'; // Adjust path if necessary
import { PermissionService } from '../../../core/services/permission.service'; // Adjust path if necessary
import { Role } from '../../../core/models/role.model';
import { Permission } from '../../../core/models/permission.model';

@Component({
  selector: 'app-role-create', // Ensure this selector is correct
  templateUrl: './create.component.html', // Ensure this HTML file exists
  styleUrls: ['./create.component.css'],
})
export class CreateComponent implements OnInit {
  roleForm!: FormGroup; // Declare roleForm as a FormGroup
  permissions: Permission[] = []; // Declare permissions array to hold all available permissions
  selectedPermissionIds: number[] = []; // Declare array to hold selected permission IDs

  constructor(
    private fb: FormBuilder,
    private roleService: RoleService,
    private permissionService: PermissionService,
    private router: Router
  ) {}

  ngOnInit(): void {
    // Initialize the form group with a 'name' control
    this.roleForm = this.fb.group({
      name: ['', Validators.required],
    });
    this.loadPermissions(); // Load all available permissions for the checkboxes
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
    const permissionId = parseInt(target.value, 10);

    if (target.checked) {
      this.selectedPermissionIds.push(permissionId);
    } else {
      this.selectedPermissionIds = this.selectedPermissionIds.filter(id => id !== permissionId);
    }
  }

  /**
   * Checks if a permission ID is already selected.
   * Used to set the `checked` state of the checkboxes in the template.
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
   * Handles the form submission.
   * If the form is valid, it creates a new role with the selected permissions.
   */
  onSubmit(): void {
    if (this.roleForm.valid) {
      const newRole: Role = {
        name: this.roleForm.value.name,
        permissions: this.selectedPermissionIds // Attach the selected permission IDs
      };

      this.roleService.createRole(newRole).subscribe({
        next: (response) => {
          console.log('Rol creado:', response);
          this.router.navigate(['/roles']); // Navigate to the roles list after creation
        },
        error: (err) => {
          console.error('Error al crear rol:', err);
          // Implement user-friendly error display here
        },
      });
    }
  }
}
