import {useLocation} from 'react-router-dom';
import {useEffect} from 'react';
import {usePrevious} from '@common/utils/hooks/use-previous';

export function useScrollToTop() {
  const {pathname} = useLocation();

  const previousPathname = usePrevious(pathname);

  useEffect(() => {
    if (previousPathname !== pathname) {
      document.documentElement.scrollTo({
        top: 0,
        left: 0,
      });
    }
  }, [pathname, previousPathname]);
}
