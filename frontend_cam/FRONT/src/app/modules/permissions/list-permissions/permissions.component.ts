import { Component, OnInit  } from '@angular/core';

@Component({
  selector: 'app-permissions',
  standalone: false,
  templateUrl: './permissions.component.html',
  styleUrl: './permissions.component.css',
})
export class PermissionsComponent implements OnInit {
  permissions: any[] = [];
  paginatedPermissions: any[] = [];

  currentPage: number = 1;
  itemsPerPage: number = 5;

  ngOnInit(): void {
    // Simulamos permisos
    this.permissions = Array.from({ length: 23 }, (_, i) => ({
      id: i + 1,
      name: `Permiso ${i + 1}`,
      created_at: new Date().toISOString()
    }));

    this.updatePaginatedPermissions();
  }

  onPageChange(page: number): void {
    this.currentPage = page;
    this.updatePaginatedPermissions();
  }

  updatePaginatedPermissions(): void {
    const start = (this.currentPage - 1) * this.itemsPerPage;
    const end = start + this.itemsPerPage;
    this.paginatedPermissions = this.permissions.slice(start, end);
  }
}
