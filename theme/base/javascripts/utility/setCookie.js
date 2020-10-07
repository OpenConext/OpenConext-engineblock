import * as Cookies from 'js-cookie';

/**
 * Save a cookie, by default for 365 days
 *
 * @param content       the content you want to put in it
 * @param cookieName    the name for the cookie
 * @param expires       when the cookie expires (number)
 * @param path          on which domain to save it
 */
export const setCookie = (content, cookieName, expires = 365, path = '/') => {
  Cookies.set(cookieName, content, { expires: expires, path: path });
};
