import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { RoleService } from '../../../core/services/role.service';
import { HttpErrorResponse } from '@angular/common/http';
import { Role, Permission, ApiResponse } from '../../../core/models/role.model'; // Import ApiResponse
import { AuthService } from '../../../core/services/auth.service'; // Needed for checkPermission

@Component({
  selector: 'app-roles-index',
  standalone: false,
  templateUrl: './index.component.html',
  styleUrls: ['./index.component.css'],
})
export class IndexComponent implements OnInit {
  roles: Role[] = [];
  errorMessage: string = '';
  successMessage: string = '';

  totalItems: number = 0;
  itemsPerPage: number = 10;
  currentPage: number = 1;

  constructor(
    private roleService: RoleService,
    private router: Router,
    private authService: AuthService // Inject AuthService if checkPermission is used
  ) {}

  ngOnInit(): void {
    this.loadRoles();
  }

  loadRoles(): void {
    this.errorMessage = ''; // Clear previous errors
    // Ensure you're passing page and perPage parameters if your API is paginated
    this.roleService.getRoles(this.currentPage, this.itemsPerPage).subscribe({
      next: (response: ApiResponse<Role[]>) => { // Correct type: ApiResponse<Role[]>
        this.roles = response.data; // Access roles from the 'data' property of ApiResponse
        this.totalItems = response.total; // Get total items from the 'total' property
        this.currentPage = response.current_page; // Update current page based on response
        this.itemsPerPage = response.per_page; // Update items per page based on response
      },
      error: (err: HttpErrorResponse) => { // Explicitly type error parameter
        console.error('Error al cargar roles:', err);
        this.errorMessage = err.error?.message || 'Error al cargar los roles.';
      },
    });
  }

  deleteRole(id: number): void {
    this.errorMessage = '';
    this.successMessage = '';

    if (confirm('¿Estás seguro de que quieres eliminar este rol?')) {
      this.roleService.deleteRole(id).subscribe({
        next: (response) => {
          this.successMessage = response.message || 'Rol eliminado exitosamente.';
          this.loadRoles(); // Reload roles list
        },
        error: (err: HttpErrorResponse) => { // Explicitly type error parameter
          console.error('Error al eliminar rol:', err);
          this.errorMessage = err.error?.message || 'Error al eliminar el rol.';
        },
      });
    }
  }

  // Ensure AuthService is injected if you use this method
  // checkPermission(permissionName: string): boolean {
  //   // return this.authService.hasPermission(permissionName);
  //   return true; // Placeholder if AuthService is not injected
  // }

  getPermissionNames(permissions: Permission[]): string {
    return permissions.map((p) => p.name).join(', ');
  }

  onPageChange(page: number): void {
    this.currentPage = page;
    this.loadRoles(); // Reload roles for the new page
  }

  editRole(id: number): void {
    this.router.navigate(['/dashboard/roles', id, 'editar']); // Adjust the path to match your routes
  }
    // Método para verificar permisos (necesita AuthService)
  checkPermission(permissionName: string): boolean {
    return this.authService.hasPermission(permissionName);
  } 
 
  updatePaginatedRoles(): void { 
    const start = (this.currentPage - 1) * this.itemsPerPage;
    const end = start + this.itemsPerPage;
  }
  hasPermission(permissionName: string): boolean {
    return this.authService.hasPermission(permissionName);
  }
}
