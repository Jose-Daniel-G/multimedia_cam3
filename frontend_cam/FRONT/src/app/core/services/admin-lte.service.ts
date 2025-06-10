import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class AdminLteService {
  constructor() {}

  toggleSidebar(): void {
    const body = document.body;

    if (body.classList.contains('sidebar-open')) {
      body.classList.remove('sidebar-open');
      body.classList.add('sidebar-collapse');
    } else {
      body.classList.remove('sidebar-collapse');
      body.classList.add('sidebar-open');
    }
  }
}
