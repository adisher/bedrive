import {useEffect, useState} from 'react';

interface StoreEvent {
  detail: {
    key: string;
    newValue: any;
  };
}

export function useLocalStorage<T>(key: string, initialValue: T | null = null) {
  const [storedValue, setStoredValue] = useState<T>(() => {
    return getFromLocalStorage<T>(key, initialValue);
  });

  const setValue = (value: T | ((val: T) => T)) => {
    const valueToStore = value instanceof Function ? value(storedValue) : value;
    setStoredValue(valueToStore);
    setInLocalStorage(key, valueToStore);
    window.dispatchEvent(
      new CustomEvent('storage', {
        detail: {key, newValue: valueToStore},
      })
    );
  };

  // update state value using custom storage event. This will re-render
  // component even if local storage value was set from different hook instance
  useEffect(() => {
    const handleStorageChange = (event: StoreEvent) => {
      if (event.detail.key === key) {
        setStoredValue(event.detail.newValue);
      }
    };
    window.addEventListener('storage', handleStorageChange as any);
    return () =>
      window.removeEventListener('storage', handleStorageChange as any);
  }, [key]);

  return [storedValue, setValue] as const;
}

export function getFromLocalStorage<T>(
  key: string,
  initialValue: T | null = null
) {
  if (typeof window === 'undefined') {
    return initialValue;
  }
  try {
    const item = window.localStorage.getItem(key);
    return item != null ? JSON.parse(item) : initialValue;
  } catch (error) {
    return initialValue;
  }
}

export function setInLocalStorage<T>(key: string, value: T) {
  try {
    if (typeof window !== 'undefined') {
      window.localStorage.setItem(key, JSON.stringify(value));
    }
  } catch (error) {
    //
  }
}

export function removeFromLocalStorage(key: string) {
  try {
    if (typeof window !== 'undefined') {
      window.localStorage.removeItem(key);
    }
  } catch (error) {
    //
  }
}
