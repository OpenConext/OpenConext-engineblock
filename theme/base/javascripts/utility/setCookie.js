import * as Cookies from 'js-cookie';

/**
 * Save a cookie, by default for 365 days
 *
 * Note: httpOnly is not an option here as it's not supported by js-cookie atm.
 *
 * @param content       the content you want to put in it
 * @param cookieName    the name for the cookie
 * @param expires       when the cookie expires (number of days)
 * @param path          on which domain to save it
 * @param secure        whether or not it's a secure cookie
 * @param sameSite      whether or not it's samesite
 */
export const setCookie = (content, cookieName, expires = 365, path = '/', secure = true, sameSite = 'strict') => {
  Cookies.set(cookieName, content, { expires: expires, path: path, secure: secure, sameSite: sameSite });
};
