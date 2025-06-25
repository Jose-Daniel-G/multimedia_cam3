export interface Permission {
  id: number;
  name: string;
  guard_name: string;
  created_at: string;
  updated_at?: string;
  pivot?: { // 'pivot' is optional as it only appears within role.permissions due to many-to-many relationship
    role_id: number;
    permission_id: number;
  };
}

/**
 * Interface for a Role object as received from the API (e.g., in index/show responses).
 * This includes the full Permission objects for the 'permissions' array.
 */
export interface Role {
  id: number;
  name: string;
  guard_name: string;
  created_at?: string;
  updated_at?: string;
  permissions?: Permission[]; // When receiving, 'permissions' is an array of Permission objects
}

/**
 * Interface for the payload when CREATING a new role via API.
 * The 'permission' property here is an array of numbers (permission IDs)
 * because that's what your Laravel backend expects for syncing.
 */
export interface CreateRolePayload {
  name: string;
  guard_name: string; // Made required as your form defaults it and backend likely expects it
  permission?: number[]; // When sending to backend, 'permission' is an array of IDs
}

/**
 * Interface for the payload when UPDATING an existing role via API.
 * Similar to CreateRolePayload, but properties might be optional for partial updates,
 * and 'permission' is also an array of numbers (permission IDs).
 */
export interface UpdateRolePayload {
  name?: string;
  guard_name?: string;
  permission?: number[]; // <--- ¡Importante! 'permission' es un array de IDs, no de objetos Permission
}

/**
 * Interface for Laravel's pagination response structure.
 * This represents the structure of the *inner* 'data' object from your Postman response
 * (e.g., `response.data`).
 * @template T The type of data contained within the 'data' array inside pagination (e.g., Role[] or Permission[]).
 */
export interface PaginationData<T> {
  current_page: number;
  data: T; // This will be the actual array of items (e.g., Role[] or Permission[])
  first_page_url: string | null;
  from: number | null;
  last_page: number;
  last_page_url: string | null;
  links: { url: string | null; label: string; active: boolean }[];
  next_page_url: string | null;
  path: string;
  per_page: number;
  prev_page_url: string | null;
  to: number | null;
  total: number;
}

/**
 * Interface for the overall API response wrapper, which includes 'status' and the main 'data' property
 * that now contains the PaginationData structure.
 * @template T The type of data within the 'data' array of the PaginationData (e.g., Role[] or Permission[]).
 */
export interface ApiResponse<T> {
  status?: boolean; // Optional, as some APIs might not include it
  message?: string; // Optional, for messages like "Role updated successfully"
  data: PaginationData<T>; // This 'data' property now explicitly holds the PaginationData structure
}


// Added for general API success messages
export interface SuccessMessageResponse {
  message: string;
  data?: any; // Optional data if API returns it, can be Role for update operations sometimes
}