// src/app/models/login.model.ts

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
  email_verified_at: string | null;
  two_factor_confirmed_at: string | null;
  current_team_id: number | null;
  profile_photo_path: string | null;
  created_at: string;
  updated_at: string;
  organismo_id: number;
  status: number; // Asumo que 1 o 0, por lo que 'number' o 'boolean' si es convertidor
  profile_photo_url: string;
  // Si el backend envía roles o permisos dentro del objeto 'user', agrégalos aquí:
  // roles?: string[];
  // permissions?: string[];
}

/**
 * Interfaz para la respuesta exitosa del login que se recibe del backend.
 * Coincide exactamente con la estructura de tu token de ejemplo.
 */
export interface UsuarioLoginResponse {
  access_token: string;
  token_type: string;
  user: AuthUser; // Aquí utilizamos la interfaz AuthUser definida arriba
  // `expires_in` no está en tu ejemplo de token, por lo tanto, no se incluye.
  // Si los roles y permisos vienen fuera del objeto 'user', como en algunos casos de Laravel/Sanctum:
  // roles?: string[];
  // permissions?: string[];
}
