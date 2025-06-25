// src/app/models/login.model.ts

// import { Role } from "./role.model";

/**
 * Interfaz para los datos de solicitud de login que se envían al backend.
 * Basado en tu `login.component.ts` y la autenticación estándar de Laravel.
 */
export interface LoginRequest {
  email: string;
  password: string;
}

/**
 * Interfaz para el objeto de usuario tal como lo devuelve Laravel dentro de la respuesta de login
 * o de la petición GET /api/user.
 */
export interface AuthUser {
  id: number;
  name: string;
  email: string;
  email_verified_at: string | null; // Puede ser 'null'
  password?: string; // No debería ser enviado al frontend, pero es parte de la BD
  two_factor_secret?: string | null; // Puede ser 'null'
  two_factor_recovery_codes?: string | null; // Puede ser 'null'
  two_factor_confirmed_at: string | null; // Puede ser 'null'
  remember_token?: string | null; // Puede ser 'null'
  current_team_id: number | null; // Puede ser 'null'
  profile_photo_path: string | null; // Puede ser 'null'
  created_at: string;
  updated_at: string;
  organismo_id: number;
  status: number; // Generalmente 0 o 1
  profile_photo_url: string; // Generada en el backend para el frontend
 
  roles?: string[];
  permissions?: string[];
}
/**
 * Interfaz para la respuesta exitosa del login que se recibe del backend.
 * Coincide exactamente con la estructura de tu token de ejemplo.
 */
export interface UsuarioLoginResponse {
  access_token: string;
  token_type: string;
  user: AuthUser;
}
