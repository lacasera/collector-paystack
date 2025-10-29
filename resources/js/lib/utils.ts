/**
 * Utility function to merge class names conditionally
 * Similar to clsx or classnames but simpler
 */
export function cn(...classes: (string | undefined | null | boolean)[]): string {
    return classes.filter(Boolean).join(' ');
}