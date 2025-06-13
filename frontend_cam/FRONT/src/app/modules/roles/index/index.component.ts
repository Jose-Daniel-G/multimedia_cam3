// src/app/modules/roles/list/list.component.ts
import { Component, OnInit } from '@angular/core';
import { Role } from '../../../core/models/role.model'; // Ensure this path is correct
import { Permission } from '../../../core/models/permission.model'; // Ensure this path is correct for displaying permissions
import { RoleService } from '../../../core/services/role.service';
import { Router } from '@angular/router'; // For navigation

@Component({
  selector: 'app-role-list',
  templateUrl: './index.component.html', // Ensure this HTML file exists at the correct path
  styleUrls: ['./index.component.css'] // Create this file if you need specific styles
})
export class IndexComponent implements OnInit {
  roles: Role[] = []; // Array to hold the list of roles
  successMessage: string | null = null;
  errorMessage: string | null = null;

  // Pagination properties
  currentPage: number = 1;
  itemsPerPage: number = 10; // Default items per page
  totalItems: number = 0;

  constructor(
    private roleService: RoleService,
    private router: Router
  ) {}

  ngOnInit(): void {
    this.loadRoles(); // Load roles when the component initializes
  }

  /**
   * Fetches the list of roles from the RoleService.
   * Updates `this.roles` and `this.totalItems`.
   */
  loadRoles(): void {
    // In a real application, you'd typically pass pagination parameters
    // like this.currentPage and this.itemsPerPage to the service.
    this.roleService.getAllRoles().subscribe({
      next: (data: Role[]) => {
        this.roles = data; // Assign the received roles
        this.totalItems = data.length; // Set total items for client-side pagination
        // If doing client-side pagination, you would slice `this.roles` here
        // based on `currentPage` and `itemsPerPage` for display.
      },
      error: (err) => {
        console.error('Error loading roles:', err);
        this.errorMessage = 'Hubo un error al cargar los roles.';
      }
    });
  }

  /**
   * Navigates to the role edit page.
   * @param id The ID of the role to edit.
   */
  editRole(id?: number): void {
    if (id !== undefined) {
      this.router.navigate(['/roles', id, 'edit']);
    } else {
      console.warn('Cannot edit role: ID is undefined.');
    }
  }

  /**
   * Deletes a role after user confirmation.
   * IMPORTANT: Replace `confirm()` with a custom modal UI in a production app.
   * @param id The ID of the role to delete.
   * @param roleName The name of the role for the confirmation message.
   */
  deleteRole(id?: number, roleName?: string): void {
    if (id === undefined) {
      console.warn('Cannot delete role: ID is undefined.');
      return;
    }

    // Using `confirm()` for demonstration; replace with a proper Angular modal
    if (confirm(`¿Estás seguro de eliminar el rol "${roleName || 'este rol'}"?`)) {
      this.roleService.deleteRole(id).subscribe({
        next: () => {
          this.successMessage = `Rol "${roleName || 'eliminado'}" eliminado exitosamente.`;
          this.errorMessage = null;
          this.loadRoles(); // Reload roles to update the list
        },
        error: (err) => {
          console.error('Error deleting role:', err);
          this.errorMessage = `Error al eliminar el rol "${roleName || 'este rol'}".`;
          this.successMessage = null;
        }
      });
    }
  }

  /**
   * Handles page change events from the PaginationComponent.
   * Reloads roles for the new page.
   * @param newPage The new page number.
   */
  onPageChange(newPage: number): void {
    this.currentPage = newPage;
    this.loadRoles();
  }

  /**
   * Helper function to join permission names for display.
   * This function now handles cases where 'permissions' might be an array of numbers (IDs)
   * or an array of Permission objects.
   * @param permissions An array of Permission objects or number IDs, or undefined.
   * @returns A comma-separated string of permission names/IDs, or 'N/A'.
   */
  getPermissionNames(permissions: (Permission | number)[] | undefined): string { // CRITICAL: Updated parameter type
    if (!permissions || permissions.length === 0) {
      return 'N/A';
    }

    // Check if the first element (if any) has a 'name' property to determine its type
    if (typeof permissions[0] === 'object' && 'name' in permissions[0]) {
      return (permissions as Permission[]).map(p => p.name).join(', ');
    } else if (typeof permissions[0] === 'number') {
      // If permissions are just IDs, display them as "ID: [ID]".
      // For a real application, you'd likely fetch the full permission names here or
      // ensure your API sends the full Permission objects with the Role.
      return (permissions as number[]).map(id => `ID: ${id}`).join(', ');
    }
    return 'N/A';
  }

  /**
   * Placeholder for permission check logic.
   * @param action The action to check permission for (e.g., 'roles.edit').
   * @returns `true` if the user has permission, `false` otherwise.
   */
  hasPermission(action: string): boolean {
    // Implement your actual authorization logic here (e.g., using an AuthService)
    return true; // For demonstration, always return true
  }
}
