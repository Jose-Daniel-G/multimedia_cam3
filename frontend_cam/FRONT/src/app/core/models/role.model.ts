import { Permission } from "./permission.model";

// This interface defines the structure of a Permission object,
// matching the fields from your 'permissions' database table.
export interface Role {
  id?: number;          // Corresponds to 'id' in the database
  name?: string;        // Corresponds to 'name' (e.g., 'home')
  guard_name?: string;  // Corresponds to 'guard_name' (e.g., 'web')
  created_at?: string;  // Corresponds to 'created_at' (e.g., '2025-05-28 17:29:30')
  updated_at?: string;  // Corresponds to 'updated_at' (e.g., '2025-05-28 17:29:30')
  // permissions?: Permission[]; // 👈 Muy importante
  permissions?: (Permission | number)[];
}
