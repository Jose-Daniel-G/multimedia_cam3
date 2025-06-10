import { Injectable } from '@angular/core';
import { CanActivate, Router } from '@angular/router';
import { AuthService } from '../services/auth.service';

@Injectable({
  providedIn: 'root'
})
export class AuthGuard implements CanActivate {

  constructor(private authService: AuthService, private router: Router) {}

  canActivate(route: any): boolean {
    const user = this.authService.getCurrentUser();

    if (!user) {
      this.router.navigate(['/login']);
      return false;
    }

    // const requiredRoles: string[] = route.data?.['roles'];
    // if (requiredRoles && !requiredRoles.includes(user.nombreRol)) {
    //   this.router.navigate(['/login']);
    //   return false;
    // }

    return true;
  }
}
