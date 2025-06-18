// src/app/modules/permissions/edit/edit.component.ts
import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router'; // Don't forget Router for navigation
import { PermissionService } from '../../../core/services/permission.service'; // Correct path to your service
import { Permission } from '../../../core/models/role.model'; // Correct path to your model

@Component({
  selector: 'app-edit',
  templateUrl: './edit.component.html', // Assuming you have an edit.component.html
  styleUrls: ['./edit.component.css'], // Assuming you have edit.component.css
})
export class EditComponent implements OnInit {
  permissionForm!: FormGroup;
  permissionId!: number;
  paginatedPermissions: Permission[] = []; // Initialize as an empty array
  // Assuming 'Permission' interface is correctly defined and imported
  currentPermission: Permission | undefined;

  constructor(
    private fb: FormBuilder,
    private permissionService: PermissionService, // Now it should be found
    private route: ActivatedRoute,
    private router: Router // Inject Router
  ) {}

  ngOnInit(): void {
    this.permissionForm = this.fb.group({
      name: ['', Validators.required],
    });

    this.route.params.subscribe((params) => {
      this.permissionId = +params['id']; // Convert string to number
      if (this.permissionId) {
        this.loadPermission(this.permissionId);
      }
    });

    this.loadAllPermissions(); // Load all permissions, if needed for a list
  }

  loadPermission(id: number): void {
    this.permissionService.getById(id).subscribe({
      next: (permission: Permission) => { // Explicitly type 'permission'
        this.currentPermission = permission;
        this.permissionForm.patchValue(permission);
      },
      error: (err) => {
        console.error('Error loading permission:', err);
        // Handle error, e.g., show an error message
      },
    });
  }

  loadAllPermissions(): void {
    this.permissionService.getAll().subscribe({
      next: (data: Permission[]) => { // Explicitly type 'data' as Permission[]
        this.paginatedPermissions = data;
      },
      error: (err) => {
        console.error('Error loading all permissions:', err);
      },
    });
  }

  onSubmit(): void {
    if (this.permissionForm.valid) {
      this.permissionService
        .update(this.permissionId, this.permissionForm.value)
        .subscribe({
          next: (response) => {
            console.log('Permiso actualizado:', response);
            this.router.navigate(['/permissions']); // Redirect to the permissions list
          },
          error: (err) => {
            console.error('Error al actualizar permiso:', err);
            // Handle error, e.g., show an error message
          },
        });
    }
  }

  deletePermission(id: number): void {
    if (confirm('¿Estás seguro de que quieres eliminar este permiso?')) {
      this.permissionService.delete(id).subscribe({
        next: () => {
          console.log('Permiso eliminado:', id);
          // Update the list of permissions after deletion
          this.paginatedPermissions = this.paginatedPermissions.filter(
            (p: Permission) => p.id !== id
          );
        },
        error: (err) => {
          console.error('Error al eliminar permiso:', err);
          // Handle error
        },
      });
    }
  }
}